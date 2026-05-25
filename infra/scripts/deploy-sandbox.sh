#!/bin/bash
# Deploy sandbox (versão in-repo, alvo do .github/workflows/deploy.yml após
# ativação — ver infra/scripts/README.md).
#
# Resolve o BUG da task #92: php artisan cache:clear abortava quando havia
# arquivos cache root-owned em storage/framework/cache/data/. Aqui:
#   1. Limpamos preventivamente o que deployer consegue deletar (find -user deployer).
#   2. cache:clear roda em modo defensivo (|| true) — root-owned residual fica,
#      mas o deploy não aborta mais.
#
# Estrutura idêntica à versão anterior em /home/deployer/deploy-sandbox.sh
# com as mudanças mínimas pra eliminar o ponto de aborto.
set -e
APP_DIR=/home/twoclicks.com.br/twoclicksdocs-sandbox
DEPLOY_SECRET="deploy-twoclicks-sandbox-bypass"

cd "$APP_DIR"

# Garante php artisan up no fim, mesmo se algo abortar no meio.
trap 'cd "$APP_DIR" && php artisan up 2>/dev/null || true' EXIT

php artisan down --render="errors::503" --secret="$DEPLOY_SECRET" || true

git fetch origin
git reset --hard origin/sand

composer install --no-dev --optimize-autoloader --no-interaction 2>&1

php artisan migrate --force 2>&1

# Cache cleanup defensivo (task #92): remove o que deployer consegue
# antes do cache:clear, e torna cache:clear non-fatal pra residual root-owned.
find storage/framework/cache/data -mindepth 1 -user deployer -delete 2>/dev/null || true

php artisan config:clear
php artisan route:clear
php artisan cache:clear || true
php artisan view:clear

sudo /usr/bin/supervisorctl restart horizon-sandbox

[ -x "$APP_DIR/infra/claude/sync.sh" ] && bash "$APP_DIR/infra/claude/sync.sh"

php artisan up

echo "DEPLOY SANDBOX OK"
