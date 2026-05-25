<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * Reescreve webhook_urls hardcoded pra produção (`https://docs.twoclicks.com.br
 * /api/webhook/code`) pra apontarem ao APP_URL do ambiente corrente.
 *
 * Causa raiz: dump prod→sandbox copia `task_statuses` integralmente, incluindo
 * `webhook_url`. Sandbox/local rodavam acionando webhook em prod (que respondia
 * 401 por divergência de WEBHOOK_CODE_SECRET).
 *
 * Estratégia:
 *  - Em prod (app.url == 'https://docs.twoclicks.com.br'): no-op por guard.
 *  - Em qualquer outro ambiente (sandbox/local): UPDATE só nas linhas que
 *    apontam pra URL de prod, reescrevendo pra `<APP_URL>/api/webhook/code`.
 *
 * Idempotente: rodar várias vezes não causa efeito além da primeira.
 *
 * Originada de: task #101 (achado lateral da #100).
 */
return new class extends Migration
{
    private const PROD_WEBHOOK = 'https://docs.twoclicks.com.br/api/webhook/code';

    public function up(): void
    {
        $appUrl = rtrim((string) Config::get('app.url'), '/');

        // Guard: se estamos no domínio de prod (e não no subdomínio sandbox),
        // não reescrever nada. Mantém prod intocado.
        if ($appUrl === 'https://docs.twoclicks.com.br') {
            return;
        }

        $newWebhook = $appUrl . '/api/webhook/code';

        // Defensivo: não tem sentido reescrever pra prod (cobre o caso de
        // alguém rodar a migration com app.url quebrado).
        if ($newWebhook === self::PROD_WEBHOOK) {
            return;
        }

        DB::connection('tc_doc')
            ->table('task_statuses')
            ->where('webhook_url', self::PROD_WEBHOOK)
            ->update([
                'webhook_url' => $newWebhook,
                'updated_at'  => now(),
            ]);
    }

    public function down(): void
    {
        // No-op por design: reverter restauraria a URL de prod no sandbox,
        // recriando o bug. Migrations de reconciliação não revertem.
    }
};
