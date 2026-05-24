<?php

namespace App\Jobs;

use App\Models\SandboxDump;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * Restaura o banco do sandbox (`tc_doc_sandbox`) a partir de um dump do banco
 * de produção (`tc_doc`). Roda como worker no Horizon (fila default).
 *
 * Estratégia:
 *  pg_dump tc_doc --clean --if-exists --exclude-table=sandbox_dumps
 *                 --exclude-table-data=personal_access_tokens (e outras efêmeras)
 *  | psql tc_doc_sandbox
 *
 * - `--exclude-table=sandbox_dumps` (sem -data) faz o pg_dump NÃO mencionar
 *   essa tabela — o `--clean` no destino não dropa, e o histórico de dumps
 *   é preservado.
 * - Tabelas efêmeras excluídas via `--exclude-table-data` (mantém schema
 *   mas zera dados): personal_access_tokens (hashes não batem com sandbox),
 *   failed_jobs, cache, cache_locks, sessions, jobs.
 *
 * Guard: NUNCA roda em produção (`app()->environment('production')`).
 */
class RestoreSandboxFromProdDumpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 900; // 15 minutos

    /** Tabelas com schema preservado mas dados zerados no sandbox. */
    private const EXCLUDE_DATA_TABLES = [
        'personal_access_tokens',
        'failed_jobs',
        'cache',
        'cache_locks',
        'sessions',
        'jobs',
    ];

    public function __construct(
        public readonly int $sandboxDumpId,
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        if (app()->environment('production')) {
            $this->markFailed('Job recusou rodar em production — operação destrutiva no sandbox.');
            return;
        }

        $record = SandboxDump::find($this->sandboxDumpId);
        if (! $record) {
            Log::error("RestoreSandboxFromProdDumpJob: SandboxDump #{$this->sandboxDumpId} não encontrado.");
            return;
        }

        $record->update(['started_at' => now(), 'status' => 'running']);

        try {
            $cfg = $this->config();
            $output = $this->runDumpAndRestore($cfg);

            $record->update([
                'status'      => 'success',
                'finished_at' => now(),
            ]);

            Log::info("RestoreSandboxFromProdDumpJob: dump #{$this->sandboxDumpId} OK", [
                'duration_s' => $record->fresh()->durationSeconds(),
                'tail'       => substr($output, -500),
            ]);
        } catch (\Throwable $e) {
            $this->markFailed($e->getMessage(), $record);
            throw $e;
        }
    }

    private function markFailed(string $msg, ?SandboxDump $record = null): void
    {
        $record = $record ?? SandboxDump::find($this->sandboxDumpId);
        if ($record) {
            $record->update([
                'status'        => 'failed',
                'finished_at'   => now(),
                'error_message' => substr($msg, 0, 5000),
            ]);
        }
        Log::error("RestoreSandboxFromProdDumpJob: {$msg}", ['dump_id' => $this->sandboxDumpId]);
    }

    private function config(): array
    {
        return [
            'host'         => config('database.connections.tc_doc.host'),
            'port'         => config('database.connections.tc_doc.port'),
            'user'         => config('database.connections.tc_doc.username'),
            'password'     => config('database.connections.tc_doc.password'),
            'prod_db'      => config('services.sandbox_dump.prod_db', 'tc_doc'),
            'sandbox_db'   => config('database.connections.tc_doc.database'),
            'pg_dump_bin'  => config('services.sandbox_dump.pg_dump_bin', '/usr/bin/pg_dump'),
            'psql_bin'     => config('services.sandbox_dump.psql_bin', '/usr/bin/psql'),
        ];
    }

    private function runDumpAndRestore(array $cfg): string
    {
        $excludeData = '';
        foreach (self::EXCLUDE_DATA_TABLES as $t) {
            $excludeData .= ' --exclude-table-data=' . escapeshellarg($t);
        }

        $dumpCmd = sprintf(
            '%s --host=%s --port=%s --username=%s --dbname=%s --no-owner --no-privileges --clean --if-exists --exclude-table=%s%s',
            escapeshellcmd($cfg['pg_dump_bin']),
            escapeshellarg($cfg['host']),
            escapeshellarg($cfg['port']),
            escapeshellarg($cfg['user']),
            escapeshellarg($cfg['prod_db']),
            escapeshellarg('sandbox_dumps'),
            $excludeData,
        );

        $restoreCmd = sprintf(
            '%s --host=%s --port=%s --username=%s --dbname=%s --quiet --set ON_ERROR_STOP=1',
            escapeshellcmd($cfg['psql_bin']),
            escapeshellarg($cfg['host']),
            escapeshellarg($cfg['port']),
            escapeshellarg($cfg['user']),
            escapeshellarg($cfg['sandbox_db']),
        );

        $fullCmd = "set -o pipefail; {$dumpCmd} | {$restoreCmd}";

        $process = Process::fromShellCommandline(
            $fullCmd,
            null,
            ['PGPASSWORD' => $cfg['password']] + (getenv() ?: []),
            null,
            $this->timeout,
        );

        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException(
                'pg_dump|psql falhou — '
                . 'exit=' . $process->getExitCode()
                . ' stderr=' . substr($process->getErrorOutput(), 0, 2000)
            );
        }

        return $process->getOutput();
    }
}
