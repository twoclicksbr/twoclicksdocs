<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private string $projectSlug = 'docs-twoclicks';

    private function projectId(): ?int
    {
        return DB::connection('tc_doc')->table('projects')->where('slug', $this->projectSlug)->value('id');
    }

    private function table(): \Illuminate\Database\Query\Builder
    {
        return DB::connection('tc_doc')->table('task_statuses');
    }

    public function up(): void
    {
        $projectId = $this->projectId();
        if (! $projectId) {
            return;
        }

        // 1. Renomear executar-code → executar-code-twoclicks
        $this->table()
            ->where('project_id', $projectId)
            ->where('slug', 'executar-code')
            ->update([
                'slug' => 'executar-code-twoclicks',
                'name' => 'Executar - Code/TwoClicks',
                'updated_at' => now(),
            ]);

        // 2. Renomear revisao-code → revisao-twoclicks
        $this->table()
            ->where('project_id', $projectId)
            ->where('slug', 'revisao-code')
            ->update([
                'slug' => 'revisao-twoclicks',
                'name' => 'Revisão - TwoClicks',
                'updated_at' => now(),
            ]);

        // 3. Reordenar status existentes
        $this->table()
            ->where('project_id', $projectId)
            ->where('slug', 'aprovacao-twoclicks')
            ->update(['order' => 6, 'updated_at' => now()]);

        $this->table()
            ->where('project_id', $projectId)
            ->where('slug', 'concluido')
            ->update(['order' => 9, 'updated_at' => now()]);

        $this->table()
            ->where('project_id', $projectId)
            ->where('slug', 'erro-code')
            ->update(['order' => 99, 'updated_at' => now()]);

        // 4. Criar novos status (ignorar se já existirem)
        $now = now();
        $existing = $this->table()
            ->where('project_id', $projectId)
            ->whereIn('slug', ['deploy-sandbox-code', 'deploy-prod-code', 'aprovacao-prod-twoclicks'])
            ->pluck('slug')
            ->toArray();

        $toInsert = array_filter([
            in_array('deploy-sandbox-code', $existing) ? null : [
                'project_id' => $projectId, 'slug' => 'deploy-sandbox-code',
                'name' => 'Deploy Sandbox - Code', 'order' => 5, 'status' => true,
                'model' => null, 'runtime_location' => null, 'webhook_url' => null,
                'code_prompt' => null, 'created_at' => $now, 'updated_at' => $now,
            ],
            in_array('deploy-prod-code', $existing) ? null : [
                'project_id' => $projectId, 'slug' => 'deploy-prod-code',
                'name' => 'Deploy Prod - Code', 'order' => 7, 'status' => true,
                'model' => null, 'runtime_location' => null, 'webhook_url' => null,
                'code_prompt' => null, 'created_at' => $now, 'updated_at' => $now,
            ],
            in_array('aprovacao-prod-twoclicks', $existing) ? null : [
                'project_id' => $projectId, 'slug' => 'aprovacao-prod-twoclicks',
                'name' => 'Aprovação Prod - TwoClicks', 'order' => 8, 'status' => true,
                'model' => null, 'runtime_location' => null, 'webhook_url' => null,
                'code_prompt' => null, 'created_at' => $now, 'updated_at' => $now,
            ],
        ]);

        if ($toInsert) {
            $this->table()->insert(array_values($toInsert));
        }
    }

    public function down(): void
    {
        $projectId = $this->projectId();
        if (! $projectId) {
            return;
        }

        $this->table()
            ->where('project_id', $projectId)
            ->where('slug', 'executar-code-twoclicks')
            ->update(['slug' => 'executar-code', 'name' => 'Executar - Code', 'updated_at' => now()]);

        $this->table()
            ->where('project_id', $projectId)
            ->where('slug', 'revisao-twoclicks')
            ->update(['slug' => 'revisao-code', 'name' => 'Revisão - Code', 'updated_at' => now()]);

        $this->table()
            ->where('project_id', $projectId)
            ->where('slug', 'aprovacao-twoclicks')
            ->update(['order' => 5, 'updated_at' => now()]);

        $this->table()
            ->where('project_id', $projectId)
            ->where('slug', 'concluido')
            ->update(['order' => 6, 'updated_at' => now()]);

        $this->table()
            ->where('project_id', $projectId)
            ->where('slug', 'erro-code')
            ->update(['order' => 7, 'updated_at' => now()]);

        $this->table()
            ->where('project_id', $projectId)
            ->whereIn('slug', ['deploy-sandbox-code', 'deploy-prod-code', 'aprovacao-prod-twoclicks'])
            ->delete();
    }
};
