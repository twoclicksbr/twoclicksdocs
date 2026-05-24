<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Configura show_on_task e auto_execute_default para os 10 statuses do
 * fluxo v2 (sandbox + prod), conforme a tabela da spec da task #74.
 *
 * Idempotente: o UPDATE roda em todos os projetos pelos slugs (sem filtro
 * por project_id), e sobrescreve com os valores canônicos.
 */
return new class extends Migration
{
    public function up(): void
    {
        $configs = [
            ['fazer-code',               false, true],
            ['analise-code',             false, true],
            ['executar-code-twoclicks',  true,  false],
            ['revisao-twoclicks',        false, false],
            ['deploy-sandbox-code',      false, true],
            ['aprovacao-twoclicks',      false, false],
            ['deploy-prod-code',         false, true],
            ['aprovacao-prod-twoclicks', false, false],
            ['concluido',                false, false],
            ['erro-code',                false, false],
        ];

        foreach ($configs as [$slug, $show, $auto]) {
            DB::connection('tc_doc')
                ->table('task_statuses')
                ->where('slug', $slug)
                ->update([
                    'show_on_task'         => $show,
                    'auto_execute_default' => $auto,
                    'updated_at'           => now(),
                ]);
        }
    }

    public function down(): void
    {
        // Restaura defaults da migration que criou as colunas (false em ambas).
        DB::connection('tc_doc')
            ->table('task_statuses')
            ->whereIn('slug', [
                'fazer-code', 'analise-code', 'executar-code-twoclicks',
                'revisao-twoclicks', 'deploy-sandbox-code', 'aprovacao-twoclicks',
                'deploy-prod-code', 'aprovacao-prod-twoclicks', 'concluido', 'erro-code',
            ])
            ->update([
                'show_on_task'         => false,
                'auto_execute_default' => false,
                'updated_at'           => now(),
            ]);
    }
};
