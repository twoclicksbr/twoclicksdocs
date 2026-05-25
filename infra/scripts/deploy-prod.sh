#!/bin/bash
# Deploy prod (versão in-repo). Ver infra/scripts/README.md e infra/scripts/deploy-sandbox.sh
# pro contexto. Difere apenas em APP_DIR, secret, branch alvo (prod) e supervisor target.
set -e
APP_DIR=/home/twoclicks.com.br/twoclicksdocs
DEPLOY_SECRET="deploy-twoclicks-prod-bypass"

cd "$APP_DIR"

trap 'cd "$APP_DIR" && php artisan up 2>/dev/null || true' EXIT

php artisan down --render="errors::503" --secret="$DEPLOY_SECRET" || true

git fetch origin
git reset --hard origin/prod

composer install --no-dev --optimize-autoloader --no-interaction 2>&1

php artisan migrate --force 2>&1

find storage/framework/cache/data -mindepth 1 -user deployer -delete 2>/dev/null || true

php artisan config:clear
php artisan route:clear
php artisan cache:clear || true
php artisan view:clear

sudo /usr/bin/supervisorctl restart horizon-prod

[ -x "$APP_DIR/infra/claude/sync.sh" ] && bash "$APP_DIR/infra/claude/sync.sh"

php artisan up

echo "DEPLOY PROD OK"
