<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'tc_doc';

    private string $sandboxScript = <<<'BASH'
#!/bin/bash
set -e
STARTED_AT=$(date -u +%Y-%m-%dT%H:%M:%SZ)
cd /home/twoclicks.com.br/twoclicksdocs-sandbox/
git fetch origin
BRANCH=$(git branch -r | grep "task-${TASK_ID}-" | head -1 | sed 's|.*origin/||' | tr -d ' ')
if [ -z "$BRANCH" ]; then
  FA=$(date -u +%Y-%m-%dT%H:%M:%SZ)
  curl -s -X POST "${API_URL}/api/doc/tasks/${TASK_ID}/details" \
    -H "Authorization: Bearer ${API_TOKEN}" -H "Content-Type: application/json" \
    -d "{\"task_status_id\":46,\"prompt\":\"shell-executor\",\"resumo\":\"Erro: branch task-${TASK_ID}-* nao encontrada\",\"started_at\":\"${STARTED_AT}\",\"finished_at\":\"${FA}\"}"
  curl -s -X POST "${API_URL}/api/doc/tasks/${TASK_ID}/transition" \
    -H "Authorization: Bearer ${API_TOKEN}" -H "Content-Type: application/json" \
    -d '{"task_status_slug":"erro-code"}'
  exit 1
fi
gh pr create --base develop --head "$BRANCH" --title "task #${TASK_ID}" --body "" 2>/dev/null || true
gh pr merge "$BRANCH" --admin --squash --delete-branch
gh pr create --base sand --head develop --title "deploy: task #${TASK_ID}" --body "" 2>/dev/null || true
gh pr merge develop --admin --squash
FA=$(date -u +%Y-%m-%dT%H:%M:%SZ)
curl -s -X POST "${API_URL}/api/doc/tasks/${TASK_ID}/details" \
  -H "Authorization: Bearer ${API_TOKEN}" -H "Content-Type: application/json" \
  -d "{\"task_status_id\":46,\"prompt\":\"shell-executor\",\"resumo\":\"PRs merged develop+sand. CI acionado.\",\"started_at\":\"${STARTED_AT}\",\"finished_at\":\"${FA}\"}"
curl -s -X POST "${API_URL}/api/doc/tasks/${TASK_ID}/transition" \
  -H "Authorization: Bearer ${API_TOKEN}" -H "Content-Type: application/json" \
  -d '{"task_status_slug":"aguardando-deploy-sandbox"}'
BASH;

    private string $prodScript = <<<'BASH'
#!/bin/bash
set -e
STARTED_AT=$(date -u +%Y-%m-%dT%H:%M:%SZ)
gh pr create --base prod --head sand --title "deploy: task #${TASK_ID}" --body "" 2>/dev/null || true
gh pr merge sand --admin --squash
FA=$(date -u +%Y-%m-%dT%H:%M:%SZ)
curl -s -X POST "${API_URL}/api/doc/tasks/${TASK_ID}/details" \
  -H "Authorization: Bearer ${API_TOKEN}" -H "Content-Type: application/json" \
  -d "{\"task_status_id\":47,\"prompt\":\"shell-executor\",\"resumo\":\"PR merged para prod. CI acionado.\",\"started_at\":\"${STARTED_AT}\",\"finished_at\":\"${FA}\"}"
curl -s -X POST "${API_URL}/api/doc/tasks/${TASK_ID}/transition" \
  -H "Authorization: Bearer ${API_TOKEN}" -H "Content-Type: application/json" \
  -d '{"task_status_slug":"aguardando-deploy-prod"}'
BASH;

    public function up(): void
    {
        DB::connection('tc_doc')->table('task_statuses')->where('id', 46)->update([
            'executor_type' => 'shell',
            'code_prompt'   => $this->sandboxScript,
        ]);

        DB::connection('tc_doc')->table('task_statuses')->where('id', 47)->update([
            'executor_type' => 'shell',
            'code_prompt'   => $this->prodScript,
        ]);
    }

    public function down(): void
    {
        DB::connection('tc_doc')->table('task_statuses')
            ->whereIn('id', [46, 47])
            ->update(['executor_type' => 'code']);
    }
};
