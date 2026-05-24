<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adiciona o status terminal `cancelado` (order 98) a todos os projetos
 * que tenham o fluxo v2 ativo. Distingue tasks obsoletas/abandonadas de:
 *  - `concluido` (order 9, sucesso)
 *  - `erro-code` (order 99, falha técnica)
 *
 * Idempotente: insere apenas se (project_id, slug) ainda não existe.
 *
 * Estado terminal: sem webhook, sem code_prompt, sem auto-execução,
 * show_on_task=false. Transição apenas manual via admin/MCP.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // Identifica projetos elegíveis: têm pelo menos um status do fluxo v2
        // (presença de `fazer-code` é o canário). Garante que a inserção só
        // acontece em projetos onde o fluxo v2 está configurado — não polui
        // outros projetos que possam existir sem esse fluxo.
        $eligibleProjectIds = DB::connection('tc_doc')
            ->table('task_statuses')
            ->where('slug', 'fazer-code')
            ->whereNull('deleted_at')
            ->pluck('project_id')
            ->unique()
            ->all();

        foreach ($eligibleProjectIds as $projectId) {
            $exists = DB::connection('tc_doc')
                ->table('task_statuses')
                ->where('project_id', $projectId)
                ->where('slug', 'cancelado')
                ->exists();

            if ($exists) {
                continue;
            }

            DB::connection('tc_doc')->table('task_statuses')->insert([
                'project_id'           => $projectId,
                'name'                 => 'Cancelado',
                'slug'                 => 'cancelado',
                'color'                => '#6B7280',
                'model'                => null,
                'runtime_location'     => null,
                'webhook_url'          => null,
                'code_prompt'          => null,
                'show_on_task'         => false,
                'auto_execute_default' => false,
                'order'                => 98,
                'status'               => true,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::connection('tc_doc')
            ->table('task_statuses')
            ->where('slug', 'cancelado')
            ->delete();
    }
};
