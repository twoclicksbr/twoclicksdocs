## 🚨 REGRA CRÍTICA: NUNCA ALTERAR PRODUÇÃO IN-PLACE

Em hipótese alguma o Code pode modificar o ambiente de produção diretamente. Toda mudança em prod deve passar pelo fluxo v2:

1. Branch a partir de `develop` no diretório SANDBOX (`/home/twoclicks.com.br/twoclicksdocs-sandbox/`)
2. Implementação e validação em sandbox
3. PR feature → develop
4. PR develop → sand → auto-deploy sandbox
5. Validação em sandbox
6. PR sand → prod → auto-deploy produção

**Proibido em produção (diretório `/home/twoclicks.com.br/twoclicksdocs/`):**
- Editar qualquer arquivo de código, config, migration, seeder
- Rodar `php artisan migrate`, `db:seed`, ou qualquer comando que altere estado
- Editar `/etc/`, `/home/deployer/`, sudoers, supervisor configs, scripts de deploy
- `git checkout`, `git pull`, `git merge` direto no working tree de prod
- `composer install`, `npm install`, qualquer instalação de dependência

**Permitido em produção (somente leitura/diagnóstico):**
- `cat`, `tail`, `grep`, `ls`, `ps`, `supervisorctl status`
- `php artisan migrate:status` (só leitura)
- `git log`, `git status`, `git diff` (só leitura)

**Se identificar um problema em prod que precisa ser corrigido:**
- NÃO corrige in-place, mesmo que "seja só uma linha"
- NÃO justifica com "pragmatismo" ou "destravar fluxo"
- Cria/atualiza uma task no Docs TwoClicks descrevendo o problema
- Implementa em sandbox seguindo o fluxo v2
- Reporta no task_detail e aguarda merge

**Única exceção:** comando `supervisorctl restart horizon-prod` quando explicitamente solicitado pelo Alex em uma task ativa, e somente esse comando (não outros).
