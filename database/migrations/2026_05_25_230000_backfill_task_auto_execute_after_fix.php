<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill task_auto_execute_statuses após o fix da task #100, que removeu
 * a dependência de show_on_task=false no TaskAutoExecuteService. Garante
 * que toda task tenha entry no pivot pra todos os statuses do seu projeto
 * com auto_execute_default=true, idempotente via ON CONFLICT DO NOTHING.
 *
 * Necessário porque entre 25/05/2026 23:11 (mudança manual de show_on_task
 * pra true em todos os statuses) e a aplicação deste fix, tasks criadas
 * via API ficaram com pivot vazio — observer rodou mas filtro retornou [].
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('tc_doc')->statement(<<<'SQL'
            INSERT INTO task_auto_execute_statuses (task_id, task_status_id)
            SELECT t.id, ts.id
            FROM tasks t
            JOIN task_statuses ts ON ts.project_id = t.project_id
            WHERE ts.auto_execute_default = true
              AND ts.deleted_at IS NULL
              AND t.deleted_at IS NULL
            ON CONFLICT (task_id, task_status_id) DO NOTHING
        SQL);
    }

    public function down(): void
    {
        // No-op por design: não é possível distinguir entries do backfill
        // de marcações manuais posteriores. Igual aos backfills 033802 e
        // 2026_05_25_000000.
    }
};
