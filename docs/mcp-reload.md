# MCP Reload Hook

Quando um token é criado ou revogado via admin (`/admin/projetos/{id}`), o Laravel **notifica os MCP servers** para recarregarem suas variáveis de ambiente sem precisar de restart manual.

## Por que existe

O MCP server é um processo Node.js de longa duração. Ele chama `dotenv.config()` no boot, então tokens novos no `.env` (lá no servidor MCP) não são vistos até que alguém reinicie o processo (`pm2 restart`). Isso causava atraso entre "gerou token" e "Claude consegue usar".

A solução é um endpoint no MCP que recarrega o `.env` em runtime (`dotenv.config({ override: true })`), invocado pelo Laravel a cada mudança de token.

## Componentes

| Componente | Onde |
|---|---|
| Endpoint `POST /reload-tokens` autenticado por `X-Reload-Secret` | **MCP server** (repo `twoclicksbr/twoclicksdocs-mcp`, Node.js) — escopo separado |
| Service `App\Services\McpReloadService` | **Este repo** (Laravel) |
| Hook em `ProjetoController::createToken` e `revokeToken` | **Este repo** (Laravel) |
| Feedback visual no `show.blade.php` da projeto | **Este repo** (Laravel) |

## Configuração

No `.env` do Laravel (ver `.env.example`):

```
MCP_RELOAD_SECRET=<segredo-compartilhado-com-os-MCPs>
MCP_RELOAD_URL_SANDBOX=https://mcp-sandbox.twoclicks.com.br/reload-tokens
MCP_RELOAD_URL_PROD=https://mcp.twoclicks.com.br/reload-tokens
```

`MCP_RELOAD_SECRET` é o shared secret enviado no header `X-Reload-Secret` na chamada. Deve bater com o secret do `.env` do MCP server.

URLs vazias são **skipadas silenciosamente** — útil enquanto um dos MCPs ainda não tem o endpoint implementado.

## Comportamento

`App\Services\McpReloadService::reload()` é chamado em:
- `POST /admin/projetos/{id}/tokens` (criação)
- `DELETE /admin/projetos/{id}/tokens/{tokenId}` (revogação)

Para cada URL configurada:
- Sucesso (2xx) → badge verde "ok" na flash session.
- Falha (não-2xx, timeout, DNS, etc.) → badge vermelho "failed: <mensagem>" + `Log::warning`.
- URL vazia → badge cinza "skipped: URL não configurada".

**A falha NÃO bloqueia o usuário.** O token continua válido no DB; só pode levar até o próximo restart do MCP afetado para começar a ser aceito por lá.

## Como debugar

1. Falha de auth (401): conferir que `MCP_RELOAD_SECRET` do Laravel === secret do MCP server.
2. Falha de rede: testar manualmente:
   ```
   curl -X POST https://mcp-sandbox.twoclicks.com.br/reload-tokens \
     -H "X-Reload-Secret: <secret>" -i
   ```
3. Skip silencioso: confirmar que a URL não está vazia em `config('twoclicks.mcp_reload.urls')`.
4. Log de falhas: `tail -f storage/logs/laravel.log | grep McpReloadService`.

## Pendências

- Endpoint MCP `POST /reload-tokens` ainda **não está implementado** no repo do MCP — escopo separado (parte 1 da task #67, requer execução no repo `twoclicksbr/twoclicksdocs-mcp`, que serve produção e precisa de autorização explícita).
- Até lá, o hook do Laravel apenas reporta `skipped` na UI (URLs vazias por padrão no `.env`).
