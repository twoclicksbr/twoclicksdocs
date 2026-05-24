<?php

namespace App\Jobs;

use App\Models\SandboxDump;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * Restaura o banco do sandbox (`tc_doc_sandbox`) a partir de um dump do banco
 * de produção (`tc_doc`). Roda como worker no Horizon (fila default).
 *
 * Fluxo:
 *  1. Backup das tabelas efêmeras do sandbox (preserva tokens/sessions/etc).
 *  2. DROP CASCADE manual de tudo no sandbox EXCETO sandbox_dumps (preserva
 *     histórico; necessário porque sandbox pode ter tabelas que prod ainda
 *     não tem — deploys vão pra sandbox antes).
 *  3. pg_dump tc_doc --exclude-table=sandbox_dumps
 *                    --exclude-table-data=<efêmeras> | psql tc_doc_sandbox
 *  4. TRUNCATE + restore das efêmeras com os dados originais do sandbox.
 *  5. Marcar como rodadas as migrations cujas tabelas foram preservadas
 *     do restore (sandbox_dumps), para evitar que o `migrate` no passo 6
 *     tente recriá-las.
 *  6. `php artisan migrate --force` para reaplicar as migrations "adiante"
 *     do sandbox que sumiram da tabela `migrations` (que veio da prod com
 *     histórico atrasado). Sem isso, ficam órfãs: registradas no código
 *     mas com efeitos não materializados, e UPDATEs no Eloquent quebram
 *     com `column does not exist`.
 *
 * Guard: NUNCA roda em produção (`app()->environment('production')`).
 */
class RestoreSandboxFromProdDumpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 900; // 15 minutos

    /**
     * Tabelas com schema preservado mas dados do SANDBOX (não de prod).
     * O pg_dump pula os dados dessas tabelas (--exclude-table-data), e o
     * job re-injeta os dados originais via backup/restore explícito.
     *
     * Nota: `migrations` NÃO está aqui. A tabela vem do dump da prod com o
     * histórico da prod, e o passo 6 (`migrate --force`) reaplica as
     * migrations adiante. Preservar o histórico do sandbox aqui deixaria
     * migrations registradas como "rodadas" porém com efeito perdido no
     * DROP CASCADE — UPDATEs do Eloquent quebrariam com `column does not exist`.
     */
    private const EXCLUDE_DATA_TABLES = [
        'personal_access_tokens',  // tokens MCP/Code do sandbox (hashes não batem com prod)
        'failed_jobs',
        'cache',
        'cache_locks',
        'sessions',
        'jobs',
    ];

    /**
     * Migrations cuja tabela é preservada pelo job (não dropada nem restaurada
     * pelo pg_dump). O passo 5 marca cada uma como rodada na `migrations` table
     * restaurada da prod, para que o `migrate --force` do passo 6 não tente
     * recriar a tabela e falhe com `relation already exists`.
     *
     * Mapeia: nome da migration → nome da tabela criada.
     */
    private const PRESERVED_TABLE_MIGRATIONS = [
        '2026_05_24_044000_create_sandbox_dumps_table' => 'sandbox_dumps',
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
        // 1. Backup das efêmeras do sandbox (tokens, sessions, etc).
        $ephemeralBackups = $this->backupEphemeralTables($cfg);

        try {
            // 2. DROP CASCADE de tudo (exceto sandbox_dumps) no sandbox.
            $this->dropAllSandboxTablesExceptHistory($cfg);

            // 3. pg_dump prod | psql sandbox
            $excludeData = '';
            foreach (self::EXCLUDE_DATA_TABLES as $t) {
                $excludeData .= ' --exclude-table-data=' . escapeshellarg($t);
            }

            $dumpCmd = sprintf(
                '%s --host=%s --port=%s --username=%s --dbname=%s --no-owner --no-privileges --exclude-table=%s%s',
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

            // bash explícito porque /bin/sh (dash) não suporta `set -o pipefail`
            $fullCmd = "set -o pipefail; {$dumpCmd} | {$restoreCmd}";

            $process = new Process(
                ['/bin/bash', '-c', $fullCmd],
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

            // 4. Restaura efêmeras (TRUNCATE + INSERT dos backups do sandbox).
            $this->restoreEphemeralTables($cfg, $ephemeralBackups);

            // 5. Marca como rodadas as migrations cujas tabelas são preservadas
            //    pelo job (não vieram no dump prod). Sem isso, o `migrate` no
            //    passo 6 tentaria recriar e falharia.
            $this->markPreservedTableMigrationsAsRan($cfg);

            // 6. Reaplica migrations adiante (estão no /database/migrations mas
            //    sumiram do histórico, que veio atrasado da prod).
            $migrateOutput = $this->runPendingMigrations();

            return $process->getOutput() . "\n--- migrate output ---\n" . $migrateOutput;
        } catch (\Throwable $e) {
            $this->cleanupBackupFiles($ephemeralBackups);
            throw $e;
        }
    }

    /**
     * pg_dump --data-only de cada efêmera EXISTENTE no sandbox em arquivos temp.
     *
     * @return array<string, string> [table => filepath]
     */
    private function backupEphemeralTables(array $cfg): array
    {
        $backups = [];
        foreach (self::EXCLUDE_DATA_TABLES as $table) {
            if (! $this->sandboxTableExists($cfg, $table)) {
                continue;
            }
            $file = tempnam(sys_get_temp_dir(), "sandbox-bak-{$table}-");
            $cmd = [
                $cfg['pg_dump_bin'],
                '--host=' . $cfg['host'],
                '--port=' . $cfg['port'],
                '--username=' . $cfg['user'],
                '--dbname=' . $cfg['sandbox_db'],
                '--data-only',
                '--no-owner',
                '--no-privileges',
                '--table=' . $table,
                '--file=' . $file,
            ];
            $proc = new Process($cmd, null, ['PGPASSWORD' => $cfg['password']] + (getenv() ?: []), null, $this->timeout);
            $proc->run();
            if (! $proc->isSuccessful()) {
                @unlink($file);
                throw new \RuntimeException("backup de {$table} falhou: " . substr($proc->getErrorOutput(), 0, 500));
            }
            $backups[$table] = $file;
        }
        return $backups;
    }

    /**
     * TRUNCATE CASCADE + psql --file pra restaurar dados do sandbox.
     * Após restore, deleta o arquivo temp.
     */
    private function restoreEphemeralTables(array $cfg, array $backups): void
    {
        foreach ($backups as $table => $file) {
            if (! is_file($file) || filesize($file) === 0) {
                @unlink($file);
                continue;
            }

            // Se a tabela não veio no dump de prod (ex: failed_jobs/jobs/cache
            // que existem no sandbox mas não em prod), pula o restore — o
            // backup é descartado e a tabela fica como prod deixou (inexistente).
            // Próxima migration vai re-criar e a tabela fica vazia (aceitável
            // para efêmeras).
            if (! $this->sandboxTableExists($cfg, $table)) {
                @unlink($file);
                continue;
            }

            try {
                $this->runPsqlQuery(
                    $cfg,
                    sprintf('TRUNCATE TABLE %s CASCADE', '"' . str_replace('"', '""', $table) . '"'),
                );
            } catch (\Throwable $e) {
                // edge case improvável (a tabela existia no check anterior) — ignora.
            }

            $restoreCmd = [
                $cfg['psql_bin'],
                '--host=' . $cfg['host'],
                '--port=' . $cfg['port'],
                '--username=' . $cfg['user'],
                '--dbname=' . $cfg['sandbox_db'],
                '--quiet',
                '--set=ON_ERROR_STOP=1',
                '--file=' . $file,
            ];
            $proc = new Process($restoreCmd, null, ['PGPASSWORD' => $cfg['password']] + (getenv() ?: []), null, $this->timeout);
            $proc->run();
            @unlink($file);
            if (! $proc->isSuccessful()) {
                throw new \RuntimeException(
                    "restore de {$table} falhou: " . substr($proc->getErrorOutput(), 0, 500)
                );
            }
        }
    }

    private function cleanupBackupFiles(array $backups): void
    {
        foreach ($backups as $file) {
            @unlink($file);
        }
    }

    private function sandboxTableExists(array $cfg, string $table): bool
    {
        $sql = sprintf(
            "SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name='%s' LIMIT 1",
            addslashes($table),
        );
        $out = trim($this->runPsqlQuery($cfg, $sql, ['-tA']));
        return $out === '1';
    }

    /**
     * Marca as migrations da PRESERVED_TABLE_MIGRATIONS como rodadas na tabela
     * `migrations` (que acabou de ser restaurada da prod, sem essas entradas).
     * Sem isso, o `migrate --force` seguinte tentaria recriar tabelas que estão
     * preservadas (ex: sandbox_dumps) e falharia com `relation already exists`.
     *
     * Insere apenas se: (a) a tabela está de fato presente no sandbox; (b) a
     * migration ainda não está registrada. Usa um batch sentinela = max+1.
     */
    private function markPreservedTableMigrationsAsRan(array $cfg): void
    {
        $maxBatchOut = trim($this->runPsqlQuery(
            $cfg,
            'SELECT COALESCE(MAX(batch), 0) FROM migrations',
            ['-tA'],
        ));
        $nextBatch = ((int) $maxBatchOut) + 1;

        foreach (self::PRESERVED_TABLE_MIGRATIONS as $migration => $table) {
            if (! $this->sandboxTableExists($cfg, $table)) {
                continue;
            }

            $checkSql = sprintf(
                "SELECT 1 FROM migrations WHERE migration = '%s' LIMIT 1",
                str_replace("'", "''", $migration),
            );
            if (trim($this->runPsqlQuery($cfg, $checkSql, ['-tA'])) === '1') {
                continue;
            }

            $insertSql = sprintf(
                "INSERT INTO migrations (migration, batch) VALUES ('%s', %d)",
                str_replace("'", "''", $migration),
                $nextBatch,
            );
            $this->runPsqlQuery($cfg, $insertSql);
        }
    }

    /**
     * Executa `php artisan migrate --force` no mesmo processo. Retorna o output
     * do comando (usado pelo log do job para mostrar quais migrations rodaram).
     */
    private function runPendingMigrations(): string
    {
        Artisan::call('migrate', ['--force' => true]);
        return Artisan::output();
    }

    /**
     * Dropa todas as tabelas do schema public EXCETO `sandbox_dumps` (CASCADE
     * para resolver FKs em qualquer ordem).
     */
    private function dropAllSandboxTablesExceptHistory(array $cfg): void
    {
        $listSql = "SELECT table_name FROM information_schema.tables "
                 . "WHERE table_schema='public' AND table_type='BASE TABLE' "
                 . "AND table_name <> 'sandbox_dumps'";

        $tables = $this->runPsqlQuery($cfg, $listSql, ['-tA']);
        $tables = array_values(array_filter(array_map('trim', explode("\n", $tables))));

        if (empty($tables)) {
            return;
        }

        $quotedList = implode(', ', array_map(
            fn ($t) => '"' . str_replace('"', '""', $t) . '"',
            $tables,
        ));
        $dropSql = "DROP TABLE IF EXISTS {$quotedList} CASCADE";

        $this->runPsqlQuery($cfg, $dropSql);
    }

    private function runPsqlQuery(array $cfg, string $sql, array $extraArgs = []): string
    {
        $cmd = array_merge(
            [
                $cfg['psql_bin'],
                '--host=' . $cfg['host'],
                '--port=' . $cfg['port'],
                '--username=' . $cfg['user'],
                '--dbname=' . $cfg['sandbox_db'],
                '--quiet',
                '--set=ON_ERROR_STOP=1',
            ],
            $extraArgs,
            ['-c', $sql],
        );

        $process = new Process(
            $cmd,
            null,
            ['PGPASSWORD' => $cfg['password']] + (getenv() ?: []),
            null,
            $this->timeout,
        );

        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException(
                'psql query falhou — '
                . 'exit=' . $process->getExitCode()
                . ' sql=' . substr($sql, 0, 200)
                . ' stderr=' . substr($process->getErrorOutput(), 0, 1000)
            );
        }

        return $process->getOutput();
    }
}
