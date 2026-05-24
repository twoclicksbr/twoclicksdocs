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
    ) {
        $this->onQueue('code');
    }

    public function handle(): void
    {
        Log::info("ProcessCodeTaskJob: iniciando task #{$this->taskId}");

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

        $prompt = str_replace('{task_id}', (string) $this->taskId, $status->code_prompt);

        $claudeBin  = config('services.claude.bin', 'claude');
        $projectDir = config('services.claude.project_dir', base_path());

        $process = new Process(
            command: [$claudeBin, '--dangerously-skip-permissions', '--print', $prompt],
            cwd: $projectDir,
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
