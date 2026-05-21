<?php

namespace App\Jobs;

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
        $claudeBin  = config('services.claude.bin', 'claude');
        $projectDir = config('services.claude.project_dir', base_path());

        Log::info("ProcessCodeTaskJob: iniciando task #{$this->taskId}");

        $process = new Process(
            command: [$claudeBin, '--dangerously-skip-permissions', '--print', "Fluxo de execução automático do TwoClicks. task_id={$this->taskId}"],
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
        }

        Log::info("ProcessCodeTaskJob: task #{$this->taskId} concluída");
    }
}
