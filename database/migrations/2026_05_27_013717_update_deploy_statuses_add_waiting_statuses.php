<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PROJECT_SLUG = 'docstwoclicks';

    private const NEW_SANDBOX_PROMPT = <<<'PROMPT'
Você é o Code VPS (Opus). Sua tarefa é fazer o deploy da task #{task_id} no ambiente sandbox.

**Antes de tudo:** capture o timestamp ISO 8601 UTC de início e guarde como `started_at` (ex: `started_at=$(date -u +%Y-%m-%dT%H:%M:%SZ)`).

**Verificação de idempotência:**
Antes de qualquer ação, chame GET /api/doc/tasks/{task_id}?expand=status e verifique se o campo task_status.slug === "deploy-sandbox-code". Se não for, registre um task_detail e encerre sem fazer nada (exit 0).

**Passos:**
1. Leia a task: GET /api/doc/tasks/{task_id} para descobrir o título
2. No repositório local do projeto (em /home/twoclicks.com.br/twoclicksdocs-sandbox/):
   a. git fetch origin
   b. Identifique a branch da task remotamente: git branch -r | grep task-{task_id}
      Se não existir: registre task_detail com erro e transicione para erro-code, exit 1
   c. Crie PR de task-{task_id}-<slug> → develop: gh pr create --base develop --head task-{task_id}-<slug> --title "<título da task>"
   d. Merge o PR: gh pr merge --admin --squash --delete-branch
   e. Crie PR de develop → sand: gh pr create --base sand --head develop --title "deploy: task #{task_id}"
   f. Merge o PR: gh pr merge --admin --squash
3. Capture o `finished_at` (timestamp ISO 8601 UTC do momento atual)
4. Registre o log do deploy: POST /api/doc/tasks/{task_id}/details com { "resumo": "PRs criados e merged para develop e sand. CI de deploy sandbox acionado. Aguardando webhook do CI.", "prompt": null, "started_at": "<started_at>", "finished_at": "<finished_at>" }
5. Transfira para aguardar: POST /api/doc/tasks/{task_id}/transition com { "task_status_slug": "aguardando-deploy-sandbox" }

**CRÍTICO: NÃO executar `gh run watch`. NÃO validar HTTP do ambiente. Encerrar após a transição.**

**Em caso de erro:**
- POST /api/doc/tasks/{task_id}/details com { "resumo": "<erro>", "prompt": null, "started_at": "<started_at>", "finished_at": "<timestamp atual>" }
- POST /api/doc/tasks/{task_id}/transition com { "task_status_slug": "erro-code" }
PROMPT;

    private const NEW_PROD_PROMPT = <<<'PROMPT'
Você é o Code VPS (Opus). Sua tarefa é fazer o deploy da task #{task_id} em produção.

**Antes de tudo:** capture o timestamp ISO 8601 UTC de início e guarde como `started_at` (ex: `started_at=$(date -u +%Y-%m-%dT%H:%M:%SZ)`).

**Verificação de idempotência:**
Antes de qualquer ação, chame GET /api/doc/tasks/{task_id}?expand=status e verifique se o campo task_status.slug === "deploy-prod-code". Se não for, registre um task_detail e encerre sem fazer nada (exit 0).

**Passos:**
1. Leia a task: GET /api/doc/tasks/{task_id}
2. Execute o merge sand → prod:
   a. Crie PR de sand → prod: gh pr create --base prod --head sand --title "deploy: task #{task_id}"
   b. Merge o PR: gh pr merge --admin --squash
3. Capture o `finished_at` (timestamp ISO 8601 UTC do momento atual)
4. Registre o log do deploy: POST /api/doc/tasks/{task_id}/details com { "resumo": "PR merged para prod. CI de deploy produção acionado. Aguardando webhook do CI.", "prompt": null, "started_at": "<started_at>", "finished_at": "<finished_at>" }
5. Transfira para aguardar: POST /api/doc/tasks/{task_id}/transition com { "task_status_slug": "aguardando-deploy-prod" }

**CRÍTICO: NÃO executar `gh run watch`. NÃO validar HTTP do ambiente. Encerrar após a transição.**

**Em caso de erro:**
- POST /api/doc/tasks/{task_id}/details com { "resumo": "<erro>", "prompt": null, "started_at": "<started_at>", "finished_at": "<timestamp atual>" }
- POST /api/doc/tasks/{task_id}/transition com { "task_status_slug": "erro-code" }
PROMPT;

    private const OLD_SANDBOX_PROMPT = <<<'PROMPT'
Você é o Code VPS (Sonnet). Sua tarefa é fazer o deploy da task #{task_id} no ambiente sandbox.

**Antes de tudo:** capture o timestamp ISO 8601 UTC de início e guarde como `started_at` (ex: `started_at=$(date -u +%Y-%m-%dT%H:%M:%SZ)`).

**Verificação de idempotência:**
Antes de qualquer ação, chame GET /api/doc/tasks/{task_id}?expand=status e verifique se o campo task_status.slug === "deploy-sandbox-code". Se não for, encerre sem fazer nada.

**Passos:**
1. Leia a task: GET /api/doc/tasks/{task_id} para descobrir o número da task
2. No repositório local do projeto (em /home/twoclicks.com.br/twoclicksdocs-sandbox/):
   a. Identifique a branch da task: git branch -a | grep task-{task_id}
   b. Push da branch para origin: git push origin task-{task_id}-<slug>
   c. Crie PR de task-{task_id}-<slug> → develop via API do GitHub (gh pr create)
   d. Merge o PR: gh pr merge --merge --admin
   e. Crie PR de develop → sand: gh pr create --base sand --head develop
   f. Merge o PR: gh pr merge --merge --admin
3. Aguarde o CI deploy sandbox concluir (monitore com gh run watch ou aguarde ~60s)
4. Valide que o sandbox está respondendo: curl -s -o /dev/null -w '%{http_code}' https://api.sandbox.twoclicks.com.br/
5. Capture o `finished_at` (timestamp ISO 8601 UTC do momento atual)
6. Registre o log do deploy: POST /api/doc/tasks/{task_id}/details com { "resumo": "<resultado do deploy, commits, PRs criados>", "prompt": null, "started_at": "<started_at>", "finished_at": "<finished_at>" }
7. Transfira para aprovação: POST /api/doc/tasks/{task_id}/transition com { "task_status_slug": "aprovacao-twoclicks" }

**Em caso de erro:**
- POST /api/doc/tasks/{task_id}/details com { "resumo": "<erro>", "prompt": null, "started_at": "<started_at>", "finished_at": "<timestamp atual>" }
- POST /api/doc/tasks/{task_id}/transition com { "task_status_slug": "erro-code" }
PROMPT;

    private const OLD_PROD_PROMPT = <<<'PROMPT'
Você é o Code VPS (Sonnet). Sua tarefa é fazer o deploy da task #{task_id} em produção.

**Antes de tudo:** capture o timestamp ISO 8601 UTC de início e guarde como `started_at` (ex: `started_at=$(date -u +%Y-%m-%dT%H:%M:%SZ)`).

**Verificação de idempotência:**
Antes de qualquer ação, chame GET /api/doc/tasks/{task_id}?expand=status e verifique se o campo task_status.slug === "deploy-prod-code". Se não for, encerre sem fazer nada.

**Passos:**
1. Leia a task: GET /api/doc/tasks/{task_id}
2. Execute o merge sand → prod:
   a. Crie PR de sand → prod: gh pr create --base prod --head sand --title "deploy: task #{task_id}"
   b. Merge o PR: gh pr merge --merge --admin
3. Aguarde o CI deploy produção concluir (monitore com gh run watch ou aguarde ~60s)
4. Valide que produção está respondendo: curl -s -o /dev/null -w '%{http_code}' https://docs.twoclicks.com.br/
5. Capture o `finished_at` (timestamp ISO 8601 UTC do momento atual)
6. Registre o log do deploy: POST /api/doc/tasks/{task_id}/details com { "resumo": "<resultado do deploy prod, PR criado, status HTTP>", "prompt": null, "started_at": "<started_at>", "finished_at": "<finished_at>" }
7. Transfira para aprovação prod: POST /api/doc/tasks/{task_id}/transition com { "task_status_slug": "aprovacao-prod-twoclicks" }

**Em caso de erro:**
- POST /api/doc/tasks/{task_id}/details com { "resumo": "<erro>", "prompt": null, "started_at": "<started_at>", "finished_at": "<timestamp atual>" }
- POST /api/doc/tasks/{task_id}/transition com { "task_status_slug": "erro-code" }
PROMPT;

    public function up(): void
    {
        // Make person_id nullable in task_details (webhook calls have no person)
        DB::connection('tc_doc')->statement(
            'ALTER TABLE task_details ALTER COLUMN person_id DROP NOT NULL'
        );

        $projectId = DB::connection('tc_doc')
            ->table('projects')
            ->where('slug', self::PROJECT_SLUG)
            ->value('id');

        // Shift orders ≥ 6 up by 1 to make room for aguardando-deploy-sandbox (order=6)
        DB::connection('tc_doc')
            ->table('task_statuses')
            ->where('project_id', $projectId)
            ->where('order', '>=', 6)
            ->increment('order');

        // Shift orders ≥ 9 (now deploy-prod-code is 8, aprovacao-prod-twoclicks is 9) up by 1
        // to make room for aguardando-deploy-prod (order=9)
        DB::connection('tc_doc')
            ->table('task_statuses')
            ->where('project_id', $projectId)
            ->where('order', '>=', 9)
            ->increment('order');

        // Update deploy-sandbox-code: remove gh run watch / curl, transition to aguardando-deploy-sandbox
        DB::connection('tc_doc')
            ->table('task_statuses')
            ->where('project_id', $projectId)
            ->where('slug', 'deploy-sandbox-code')
            ->update([
                'model'       => 'opus',
                'code_prompt' => self::NEW_SANDBOX_PROMPT,
                'updated_at'  => now(),
            ]);

        // Update deploy-prod-code: remove gh run watch / curl, transition to aguardando-deploy-prod
        DB::connection('tc_doc')
            ->table('task_statuses')
            ->where('project_id', $projectId)
            ->where('slug', 'deploy-prod-code')
            ->update([
                'model'       => 'opus',
                'code_prompt' => self::NEW_PROD_PROMPT,
                'updated_at'  => now(),
            ]);

        $now = now();

        // Insert aguardando-deploy-sandbox (order=6, between deploy-sandbox-code:5 and aprovacao-twoclicks:7)
        DB::connection('tc_doc')
            ->table('task_statuses')
            ->insert([
                'name'                 => 'Aguardando Deploy Sandbox',
                'slug'                 => 'aguardando-deploy-sandbox',
                'color'                => '#F59E0B',
                'order'                => 6,
                'status'               => true,
                'project_id'           => $projectId,
                'model'                => null,
                'runtime_location'     => null,
                'webhook_url'          => null,
                'code_prompt'          => null,
                'show_on_task'         => true,
                'auto_execute_default' => false,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);

        // Insert aguardando-deploy-prod (order=9, between deploy-prod-code:8 and aprovacao-prod-twoclicks:10)
        DB::connection('tc_doc')
            ->table('task_statuses')
            ->insert([
                'name'                 => 'Aguardando Deploy Prod',
                'slug'                 => 'aguardando-deploy-prod',
                'color'                => '#F59E0B',
                'order'                => 9,
                'status'               => true,
                'project_id'           => $projectId,
                'model'                => null,
                'runtime_location'     => null,
                'webhook_url'          => null,
                'code_prompt'          => null,
                'show_on_task'         => true,
                'auto_execute_default' => false,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);
    }

    public function down(): void
    {
        $projectId = DB::connection('tc_doc')
            ->table('projects')
            ->where('slug', self::PROJECT_SLUG)
            ->value('id');

        // Remove the new waiting statuses
        DB::connection('tc_doc')
            ->table('task_statuses')
            ->where('project_id', $projectId)
            ->whereIn('slug', ['aguardando-deploy-sandbox', 'aguardando-deploy-prod'])
            ->delete();

        // Restore original code_prompts
        DB::connection('tc_doc')
            ->table('task_statuses')
            ->where('project_id', $projectId)
            ->where('slug', 'deploy-sandbox-code')
            ->update([
                'model'       => 'sonnet',
                'code_prompt' => self::OLD_SANDBOX_PROMPT,
                'updated_at'  => now(),
            ]);

        DB::connection('tc_doc')
            ->table('task_statuses')
            ->where('project_id', $projectId)
            ->where('slug', 'deploy-prod-code')
            ->update([
                'model'       => 'sonnet',
                'code_prompt' => self::OLD_PROD_PROMPT,
                'updated_at'  => now(),
            ]);

        // Restore orders: shift back
        // First: orders >= 9 were shifted up twice total (+2), shift back -1 for the second shift
        DB::connection('tc_doc')
            ->table('task_statuses')
            ->where('project_id', $projectId)
            ->where('order', '>=', 10)
            ->decrement('order');

        // Then: orders >= 7 were shifted up by 1, shift back -1
        DB::connection('tc_doc')
            ->table('task_statuses')
            ->where('project_id', $projectId)
            ->where('order', '>=', 7)
            ->decrement('order');

        // Restore person_id NOT NULL constraint
        DB::connection('tc_doc')->statement(
            'ALTER TABLE task_details ALTER COLUMN person_id SET NOT NULL'
        );
    }
};
