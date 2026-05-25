#!/bin/bash
# Sincroniza infra/claude/CLAUDE.md (regras globais do Code) para o home do
# usuário corrente — o claude CLI carrega ~/.claude/CLAUDE.md em toda sessão.
# Idempotente.
#
# Chamado pelo .github/workflows/deploy.yml depois do deploy-{sandbox,prod}.sh,
# rodando como o usuário SSH do deploy (deployer). Cobre o caso mais comum:
# horizon (e tudo que ele invoca via Process) corre como deployer.
#
# Casos de uso ad-hoc como root precisariam de sync separado pra /root/.claude/
# (não feito aqui pra não exigir nova entrada no sudoers).
set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SRC="$SCRIPT_DIR/CLAUDE.md"

if [ ! -f "$SRC" ]; then
    echo "claude/sync.sh: SRC não encontrado em $SRC" >&2
    exit 1
fi

DEST_DIR="$HOME/.claude"
DEST="$DEST_DIR/CLAUDE.md"

mkdir -p "$DEST_DIR"
chmod 700 "$DEST_DIR"
install -m 644 "$SRC" "$DEST"
echo "claude/sync.sh: synced → $DEST (user=$(whoami))"
