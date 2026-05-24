<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Notifica os MCP servers (sandbox + prod) para recarregar variáveis de
 * ambiente após criação/revogação de tokens no admin.
 *
 * Ver docs/mcp-reload.md para o protocolo e a configuração esperada
 * em config/twoclicks.php (chave mcp_reload).
 *
 * Falha é silenciosa por design — log + retorno estruturado pra UI;
 * NUNCA bloqueia ou propaga exceção pro caller.
 */
class McpReloadService
{
    /**
     * Dispara o reload em todos os MCPs configurados (URLs vazias são ignoradas).
     *
     * @return array<string, array{status: 'ok'|'skipped'|'failed', message: string}>
     *         Chaveado pelo nome do ambiente (ex: 'sandbox', 'prod').
     */
    public function reload(): array
    {
        $cfg     = config('twoclicks.mcp_reload', []);
        $secret  = $cfg['secret'] ?? null;
        $urls    = $cfg['urls']   ?? [];
        $timeout = (int) ($cfg['timeout'] ?? 5);

        $results = [];

        foreach ($urls as $env => $url) {
            if (! $url) {
                $results[$env] = ['status' => 'skipped', 'message' => 'URL não configurada'];
                continue;
            }

            if (! $secret) {
                $results[$env] = ['status' => 'skipped', 'message' => 'MCP_RELOAD_SECRET não configurado'];
                continue;
            }

            try {
                $response = Http::withHeaders(['X-Reload-Secret' => $secret])
                    ->timeout($timeout)
                    ->post($url);

                if ($response->successful()) {
                    $results[$env] = ['status' => 'ok', 'message' => "HTTP {$response->status()}"];
                } else {
                    $msg = "HTTP {$response->status()}";
                    $results[$env] = ['status' => 'failed', 'message' => $msg];
                    Log::warning("McpReloadService: reload falhou ({$env})", [
                        'url'    => $url,
                        'status' => $response->status(),
                        'body'   => substr($response->body(), 0, 500),
                    ]);
                }
            } catch (\Throwable $e) {
                $msg = $e->getMessage();
                $results[$env] = ['status' => 'failed', 'message' => $msg];
                Log::warning("McpReloadService: reload exception ({$env})", [
                    'url'       => $url,
                    'exception' => $msg,
                ]);
            }
        }

        return $results;
    }
}
