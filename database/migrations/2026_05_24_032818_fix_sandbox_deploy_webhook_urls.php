<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrige webhook_url dos statuses deploy-sandbox-code (id=4) e
 * deploy-prod-code (id=5) no sandbox, que vinham apontando pra prod
 * (https://docs.twoclicks.com.br/api/webhook/code) por causa de um
 * hardcoded no TaskStatusV2Seeder antigo.
 *
 * Origem: task #75 (follow-up da #70/task_detail #86).
 *
 * Guard: skip se config('app.url') já é a URL de prod — protege
 * contra rodar essa migration em produção e quebrar webhooks lá.
 *
 * Idempotente: o UPDATE sobrescreve com o valor correto; rodar 2x
 * não tem efeito colateral.
 */
return new class extends Migration
{
    private const PROD_URL = 'https://docs.twoclicks.com.br';

    public function up(): void
    {
        $appUrl = rtrim((string) config('app.url'), '/');

        if ($appUrl === self::PROD_URL) {
            // Estamos em produção — migration não se aplica (statuses 4/5 não
            // existem em prod com esses slugs, e mesmo se existissem, prod já
            // aponta corretamente pra si mesma).
            return;
        }

        $webhookUrl = $appUrl . '/api/webhook/code';

        DB::connection('tc_doc')
            ->table('task_statuses')
            ->whereIn('slug', ['deploy-sandbox-code', 'deploy-prod-code'])
            ->where('webhook_url', self::PROD_URL . '/api/webhook/code')
            ->update([
                'webhook_url' => $webhookUrl,
                'updated_at'  => now(),
            ]);
    }

    public function down(): void
    {
        // Restaura URL prod nos statuses afetados — só faz se estamos no
        // mesmo ambiente onde a migration up rodou (não-prod).
        $appUrl = rtrim((string) config('app.url'), '/');

        if ($appUrl === self::PROD_URL) {
            return;
        }

        $webhookUrl = $appUrl . '/api/webhook/code';

        DB::connection('tc_doc')
            ->table('task_statuses')
            ->whereIn('slug', ['deploy-sandbox-code', 'deploy-prod-code'])
            ->where('webhook_url', $webhookUrl)
            ->update([
                'webhook_url' => self::PROD_URL . '/api/webhook/code',
                'updated_at'  => now(),
            ]);
    }
};
