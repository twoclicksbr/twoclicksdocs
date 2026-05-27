# Supervisor configs (Horizon)

Espelho dos arquivos em produção em `/etc/supervisor/conf.d/`. Mantido em git pra
sobreviver a redeploys e servir de referência canônica.

## Instalação na VPS

1. Copiar para `/etc/supervisor/conf.d/`:
   ```bash
   sudo cp infra/supervisor/horizon-prod.conf    /etc/supervisor/conf.d/
   sudo cp infra/supervisor/horizon-sandbox.conf /etc/supervisor/conf.d/
   ```
2. Aplicar:
   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl status        # confirmar RUNNING + USER=deployer
   ```

## Por que isto existe (task #86)

Antes desta task, o Horizon rodava como **root**, gerenciado por `pm2` (que
também rodava como root via `pm2-root.service`). Isso quebrava o
`ProcessCodeTaskJob` porque o CLI do Claude Code rejeita
`--dangerously-skip-permissions` quando o EUID é 0, travando toda a automação
do fluxo v2.

Migração feita:
- Removidos os apps `twoclicksdocs-horizon` e `twoclicksdocs-sandbox-horizon` do PM2
  (`pm2 delete` + `pm2 save`).
- Os apps `twoclicksdocs-mcp` e `twoclicksdocs-sandbox-mcp` continuam no PM2 (fora
  do escopo).
- Instalado pacote `supervisor` da Ubuntu; serviço `supervisor.service` enabled.
- Horizon prod e sandbox passam a rodar como `user=deployer`.

## Pré-requisitos no host

- Usuário `deployer` existe (uid 1001) e tem permissão de escrita em
  `storage/logs/`, `bootstrap/cache/` dos dois repositórios.
- `/home/deployer/.claude/` precisa existir com `.credentials.json` válido para o
  Claude CLI conseguir autenticar quando o job invocar `claude --print`.
- `claude` no PATH (`/usr/bin/claude`).
