<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\TaskStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
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
        Log::info("ProcessCodeTaskJob: iniciando task_id={$this->taskId} project={$this->projectSlug}");

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

        $executorType = $status->executor_type ?? 'code';

        $codePromptResolved = str_replace('{task_id}', (string) $this->taskId, $status->code_prompt);
        $apiBase = rtrim(config('app.url'), '/') . '/api';

        if ($executorType === 'shell') {
            $tmpFile = '/tmp/task-status-' . $this->taskId . '-' . time() . '.sh';
            // Normalize CRLF/CR to LF — bash rejects \r in tokens with syntax error (exit 2)
            $shellScript = str_replace(["\r\n", "\r"], ["\n", "\n"], $codePromptResolved);
            file_put_contents($tmpFile, $shellScript);
            chmod($tmpFile, 0755);

            $shellEnv = array_merge(getenv() ?: [], [
                'TASK_ID'   => (string) $this->taskId,
                'API_URL'   => rtrim(config('app.url'), '/'),
                'API_TOKEN' => $token,
            ]);

            $shellProcess = new Process(['bash', $tmpFile], null, $shellEnv, null, 1800);
            $shellProcess->run(fn($type, $output) => Log::info("shell[task#{$this->taskId}]: {$output}"));

            $exitCode = $shellProcess->getExitCode();
            $stdout   = mb_substr($shellProcess->getOutput(), 0, 2000);
            $stderr   = mb_substr($shellProcess->getErrorOutput(), 0, 500);

            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }

            $resumo = "Shell exit={$exitCode}. stdout: {$stdout}" . ($stderr ? " | stderr: {$stderr}" : '');

            try {
                Http::withHeaders(['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'])
                    ->timeout(10)->post("{$apiBase}/doc/tasks/{$this->taskId}/details", [
                        'task_status_id' => $status->id,
                        'resumo'         => $resumo,
                        'prompt'         => 'shell-executor',
                    ]);
            } catch (\Throwable $e) {
                Log::warning("ProcessCodeTaskJob: falha shell detail task #{$this->taskId}: {$e->getMessage()}");
            }

            if ($exitCode !== 0) {
                try {
                    Http::withHeaders(['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'])
                        ->timeout(10)->post("{$apiBase}/doc/tasks/{$this->taskId}/transition", [
                            'task_status_slug' => 'erro-code',
                        ]);
                } catch (\Throwable $e) {
                    Log::error("ProcessCodeTaskJob: falha ao transicionar erro-code task #{$this->taskId}: {$e->getMessage()}");
                }
                $this->fail(new \RuntimeException("Shell script encerrou com código {$exitCode}"));
                return;
            }

            Log::info("ProcessCodeTaskJob: shell concluido task_id={$this->taskId}");
            return;
        }

        $context = "[Contexto: task_id={$this->taskId}, expected_status_slug={$status->slug}, project_slug={$this->projectSlug}]";
        $prompt  = "{$context}\n\n{$codePromptResolved}";
        try {
            $startResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept'        => 'application/json',
            ])->timeout(10)->post("{$apiBase}/doc/tasks/{$this->taskId}/details", [
                'resumo' => "Code iniciou processamento (status: {$status->slug}).",
                'prompt' => null,
            ]);
            if (! $startResponse->successful()) {
                Log::warning("ProcessCodeTaskJob: detail de início retornou HTTP {$startResponse->status()} para task #{$this->taskId}");
            }
        } catch (\Throwable $e) {
            Log::warning("ProcessCodeTaskJob: falha ao registrar detail de início para task #{$this->taskId}: {$e->getMessage()}");
        }

        $claudeBin  = config('services.claude.bin', 'claude');
        $projectDir = config('services.claude.project_dir', base_path());

        $modelArgs = match ($status->model ?? null) {
            'sonnet' => ['--model', 'claude-sonnet-4-6'],
            'opus'   => ['--model', 'claude-opus-4-7'],
            default  => [],
        };

        Log::info("ProcessCodeTaskJob: usando model=".($status->model ?? 'default (sem flag)')." para task #{$this->taskId}");

        $command = array_merge(
            [$claudeBin, '--dangerously-skip-permissions'],
            $modelArgs,
            ['--print', $prompt],
        );

        $env = getenv() ?: [];
        $env['TWOCLICKS_API_TOKEN'] = $token;

        $process = new Process(
            command: $command,
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
            Log::info("ProcessCodeTaskJob: concluído task_id={$this->taskId} exit_code=".$process->getExitCode());
            $this->fail(new \RuntimeException("Claude CLI encerrou com código {$process->getExitCode()}"));
            return;
        }

        Log::info("ProcessCodeTaskJob: concluído task_id={$this->taskId} exit_code=".$process->getExitCode());
    }
}
