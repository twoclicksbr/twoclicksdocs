<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill #2: garante que toda task existente tenha entrada em
 * task_auto_execute_statuses para os statuses "alwaysAuto" do seu projeto
 * (show_on_task=false E auto_execute_default=true).
 *
 * Necessário porque tasks criadas via API (POST /api/doc/tasks e
 * /api/doc/tasks/bulk) e via MCP entre a feature inicial e a correção (task #88)
 * não populavam o pivot, o que impedia o webhook de disparar mesmo com a task
 * em fazer-code. Ex: tasks #85, #86, #87, #88 ficaram travadas por isso.
 *
 * O backfill original (033802) inseriu para webhook_url IS NOT NULL (mais
 * amplo); este aqui usa o critério mais estrito que daqui pra frente é
 * aplicado pelo TaskAutoExecuteObserver. Idempotente via ON CONFLICT.
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
            WHERE ts.show_on_task = false
              AND ts.auto_execute_default = true
              AND ts.deleted_at IS NULL
              AND t.deleted_at IS NULL
            ON CONFLICT (task_id, task_status_id) DO NOTHING
        SQL);
    }

    public function down(): void
    {
        // No-op por design (igual ao backfill 033802): não dá pra distinguir
        // entradas do backfill de marcações manuais posteriores.
    }
};
