<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\TaskStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class ProcessCodeTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 1800; // 30 minutos

    public function __construct(
        public readonly int $taskId,
        public readonly ?string $projectSlug = null,
    ) {
        $this->onQueue('code');
    }

    public function handle(): void
    {
        Log::info("ProcessCodeTaskJob: iniciando task #{$this->taskId} (project={$this->projectSlug})");

        if (! $this->projectSlug) {
            $msg = "ProcessCodeTaskJob: projectSlug obrigatório (task #{$this->taskId})";
            Log::error($msg);
            $this->fail(new \RuntimeException($msg));
            return;
        }

        $token = config("twoclicks.tokens.{$this->projectSlug}");
        if (! $token) {
            $msg = "ProcessCodeTaskJob: token não configurado para projectSlug='{$this->projectSlug}' (esperado em config/twoclicks.php via env TWOCLICKS_CODE_TOKEN_".strtoupper(str_replace('-', '_', $this->projectSlug)).")";
            Log::error($msg);
            $this->fail(new \RuntimeException($msg));
            return;
        }

        $task = Task::find($this->taskId);
        if (! $task) {
            $msg = "ProcessCodeTaskJob: task #{$this->taskId} não encontrada";
            Log::error($msg);
            $this->fail(new \RuntimeException($msg));
            return;
        }

        $status = TaskStatus::find($task->task_status_id);
        if (! $status || ! $status->code_prompt) {
            $msg = "ProcessCodeTaskJob: status do task #{$this->taskId} sem code_prompt (task_status_id={$task->task_status_id})";
            Log::error($msg);
            $this->fail(new \RuntimeException($msg));
            return;
        }

        $codePromptResolved = str_replace('{task_id}', (string) $this->taskId, $status->code_prompt);
        $context = "[Contexto: task_id={$this->taskId}, expected_status_slug={$status->slug}, project_slug={$this->projectSlug}]";
        $prompt  = "{$context}\n\n{$codePromptResolved}";

        $claudeBin  = config('services.claude.bin', 'claude');
        $projectDir = config('services.claude.project_dir', base_path());

        $env = getenv() ?: [];
        $env['TWOCLICKS_API_TOKEN'] = $token;

        $process = new Process(
            command: [$claudeBin, '--dangerously-skip-permissions', '--print', $prompt],
            cwd: $projectDir,
            env: $env,
            timeout: 1800,
        );

        $process->run(function (string $type, string $output) {
            Log::info("claude[task#{$this->taskId}]: {$output}");
        });

        if (! $process->isSuccessful()) {
            Log::error("ProcessCodeTaskJob: claude falhou na task #{$this->taskId}", [
                'exit_code' => $process->getExitCode(),
                'stderr'    => $process->getErrorOutput(),
            ]);
            $this->fail(new \RuntimeException("Claude CLI encerrou com código {$process->getExitCode()}"));
            return;
        }

        Log::info("ProcessCodeTaskJob: task #{$this->taskId} concluída");
    }
}
