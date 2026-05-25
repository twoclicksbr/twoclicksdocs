<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $webhookUrl = rtrim((string) config('app.url'), '/') . '/api/webhook/code';
        $now = now();

        // Resolver project_id por slug (resiliente a id hardcoded entre ambientes).
        $projectId = DB::connection('tc_doc')
            ->table('projects')
            ->where('slug', 'docstwoclicks')
            ->value('id');

        if (! $projectId) {
            // Projeto não existe no ambiente atual — noop seguro.
            return;
        }

        // ★ Ambos os UPDATEs DEVEM ter ->where('project_id', $projectId).
        // Sem o filtro, o slug 'aprovacao-twoclicks' existe em 6 projetos e
        // todos seriam sobrescritos com webhook/code_prompt do docstwoclicks,
        // quebrando os 5 projetos não-alvo (bug da tentativa anterior).

        DB::connection('tc_doc')
            ->table('task_statuses')
            ->where('slug', 'aprovacao-twoclicks')
            ->where('project_id', $projectId)
            ->update([
                'webhook_url'          => $webhookUrl,
                'model'                => 'opus',
                'runtime_location'     => 'vps',
                'auto_execute_default' => false,
                'code_prompt'          => 'Transicione a task #{task_id} para deploy-prod-code. Apenas POST /api/doc/tasks/{task_id}/transition com {"task_status_slug":"deploy-prod-code"}. Nenhuma outra ação.',
                'updated_at'           => $now,
            ]);

        DB::connection('tc_doc')
            ->table('task_statuses')
            ->where('slug', 'aprovacao-prod-twoclicks')
            ->where('project_id', $projectId)
            ->update([
                'webhook_url'          => $webhookUrl,
                'model'                => 'opus',
                'runtime_location'     => 'vps',
                'auto_execute_default' => false,
                'code_prompt'          => 'Transicione a task #{task_id} para concluido. Apenas POST /api/doc/tasks/{task_id}/transition com {"task_status_slug":"concluido"}. Nenhuma outra ação.',
                'updated_at'           => $now,
            ]);
    }

    public function down(): void
    {
        // Data fix — não reverte.
    }
};
