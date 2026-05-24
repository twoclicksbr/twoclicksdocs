<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill de compatibilidade: marca toda task existente como auto-executar
 * em todo status do mesmo projeto que tenha webhook_url setado. Mantém o
 * comportamento atual (transition disparava webhook sempre que webhook_url
 * estava preenchido) para tasks que existiam antes desta feature.
 *
 * Tasks novas (criadas após o deploy) usam a lógica via form/seeder.
 *
 * Idempotente: ON CONFLICT DO NOTHING (PK composta task_id+task_status_id).
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
            WHERE ts.webhook_url IS NOT NULL
              AND ts.deleted_at IS NULL
              AND t.deleted_at IS NULL
            ON CONFLICT (task_id, task_status_id) DO NOTHING
        SQL);
    }

    public function down(): void
    {
        // Irreversível por design — não há como saber quais entries foram do
        // backfill vs marcações manuais posteriores. Down é no-op.
    }
};
