# Deploy scripts (in-repo)

Versão canônica dos scripts de deploy. Antes (até task #92) viviam fora do
repo em `/home/deployer/deploy-{sandbox,prod}.sh`, o que tornava qualquer
ajuste uma edição in-place em prod — proibido pela regra global em
`infra/claude/CLAUDE.md`.

## Fix incluído

`storage/framework/cache/data/` podia ter arquivos root-owned (resíduo de
`php artisan` rodado como root em sessões antigas), o que fazia
`php artisan cache:clear` falhar com `ERROR Failed to clear cache` →
`set -e` abortava o deploy → `supervisorctl restart` e `sync.sh` não rodavam.

Mudanças:
1. `find ... -user deployer -delete || true` antes do cache:clear (limpa o
   que o deployer consegue).
2. `php artisan cache:clear || true` (non-fatal). Se sobrarem arquivos
   root-owned o deploy não aborta — a aplicação cuida da invalidação
   normal de cache em runtime.

## Ativação

O `.github/workflows/deploy.yml` ainda chama `/home/deployer/deploy-{sandbox,prod}.sh`
(versão antiga). Ativar este conjunto requer **uma** das duas ações abaixo,
fora do alcance autônomo do Code (gh token atual não tem `workflow` scope, e
edit in-place em `/home/deployer/` é proibido pela regra):

**Opção 1 (preferida) — Workflow → repo:** atualizar `.github/workflows/deploy.yml` pra:

```yaml
script: |
  if [ "${{ github.ref_name }}" = "sand" ]; then
    bash /home/twoclicks.com.br/twoclicksdocs-sandbox/infra/scripts/deploy-sandbox.sh
  else
    bash /home/twoclicks.com.br/twoclicksdocs/infra/scripts/deploy-prod.sh
  fi
```

Requer `gh auth refresh -s workflow` (ou um novo PAT com workflow scope).

**Opção 2 — Bootstrap one-shot dos wrappers (similar ao que a #91 fez pro sync.sh):**
adicionar ANTES do `cache:clear` em `/home/deployer/deploy-{sandbox,prod}.sh`:

```bash
[ -x "$APP_DIR/infra/scripts/safe-cache-prep.sh" ] && bash "$APP_DIR/infra/scripts/safe-cache-prep.sh"
```

(precisaria também extrair só o trecho de cache pra `infra/scripts/safe-cache-prep.sh`)

**Cleanup one-shot (independente da opção):** os 3 arquivos root-owned atuais
em `/home/twoclicks.com.br/twoclicksdocs/storage/framework/cache/data/ee/`
podem ser limpos manualmente com:

```bash
sudo find /home/twoclicks.com.br/twoclicksdocs/storage/framework/cache -not -user deployer -delete
```

(também viola in-place; mas é uma operação one-shot pra fechar o débito
técnico das sessões pré-regra).

## Conteúdo dos scripts vs versão `/home/deployer/`

Diff mínimo. O fluxo de etapas é idêntico ao das versões em `/home/deployer/`
após os bootstraps das tasks #86 (pm2→supervisor) e #91 (chamada do sync.sh).
A única adição funcional é o cache-cleanup defensivo descrito acima.
