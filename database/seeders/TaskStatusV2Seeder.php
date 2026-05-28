<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskStatusV2Seeder extends Seeder
{
    private string $projectSlug = 'docstwoclicks';
    private string $webhookUrl  = 'https://docs.twoclicks.com.br/api/webhook/code';

    public function run(): void
    {
        $projectId = DB::connection('tc_doc')
            ->table('projects')
            ->where('slug', $this->projectSlug)
            ->value('id');

        if (! $projectId) {
            $this->command->warn("Projeto '{$this->projectSlug}' não encontrado. Seeder ignorado.");
            return;
        }

        $configs   = $this->configs();
        $insertOnly = $this->insertOnlyConfigs();
        $updated   = 0;
        $inserted  = 0;

        foreach ($configs as $slug => $config) {
            DB::connection('tc_doc')
                ->table('task_statuses')
                ->where('project_id', $projectId)
                ->where('slug', $slug)
                ->update(array_merge($config, ['updated_at' => now()]));
            $updated++;
        }

        foreach ($insertOnly as $slug => $config) {
            $exists = DB::connection('tc_doc')
                ->table('task_statuses')
                ->where('project_id', $projectId)
                ->where('slug', $slug)
                ->exists();

            if (! $exists) {
                DB::connection('tc_doc')
                    ->table('task_statuses')
                    ->insert(array_merge($config, [
                        'project_id' => $projectId,
                        'slug'       => $slug,
                        'status'     => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));
                $inserted++;
            }
        }

        $this->command->info("TaskStatusV2Seeder: {$updated} status atualizados, {$inserted} inseridos para projeto '{$this->projectSlug}'.");
    }

    private function configs(): array
    {
        return [
            'fazer-code' => [
                'model'            => 'opus',
                'runtime_location' => 'vps',
                'webhook_url'      => $this->webhookUrl,
                'code_prompt'      => <<<'PROMPT'
Você é o Code VPS (Opus). Sua tarefa é interpretar o pedido da task #{task_id} em linguagem clara.

**Verificação de idempotência:**
Antes de qualquer ação, chame GET /api/doc/tasks/{task_id}?expand=status e verifique se o campo task_status.slug === "fazer-code". Se não for, encerre sem fazer nada.

**Passos:**
1. Leia o título e a descrição da task: GET /api/doc/tasks/{task_id}
2. Interprete o pedido em linguagem clara — o que precisa ser feito, qual o objetivo, quais são as restrições ou dependências mencionadas
3. Registre a interpretação: POST /api/doc/tasks/{task_id}/details com { "resumo": "<sua interpretação>", "prompt": null }
4. Transfira para análise: POST /api/doc/tasks/{task_id}/transition com { "task_status_slug": "analise-code" }

**Em caso de erro em qualquer passo:**
- Registre o erro: POST /api/doc/tasks/{task_id}/details com { "resumo": "<descrição do erro>", "prompt": null }
- Transfira para erro: POST /api/doc/tasks/{task_id}/transition com { "task_status_slug": "erro-code" }

Não leia código. Não acesse repositórios. Apenas interprete o pedido descrito na task.
PROMPT,
            ],

            'analise-code' => [
                'model'            => 'opus',
                'runtime_location' => 'vps',
                'webhook_url'      => $this->webhookUrl,
                'code_prompt'      => <<<'PROMPT'
Você é o Code VPS (Opus). Sua tarefa é validar a interpretação e gerar o prompt técnico para a task #{task_id}.

**Verificação de idempotência:**
Antes de qualquer ação, chame GET /api/doc/tasks/{task_id}?expand=status e verifique se o campo task_status.slug === "analise-code". Se não for, encerre sem fazer nada.

**Passos:**
1. Leia a task e seus detalhes: GET /api/doc/tasks/{task_id} e GET /api/doc/tasks/{task_id}/details
2. Inspecione o código relevante no ambiente sandbox (/home/twoclicks.com.br/twoclicksdocs-sandbox/) para entender o contexto atual
3. Valide se a interpretação do passo anterior está alinhada com a descrição original
4. Gere um prompt técnico detalhado que o Code local (Sonnet) usará para executar a tarefa — inclua: arquivos a modificar, lógica esperada, casos de borda, validações necessárias
5. Registre o prompt técnico e parecer de alinhamento: POST /api/doc/tasks/{task_id}/details com { "resumo": "<prompt técnico + parecer>", "prompt": null }
6. Transfira para execução: POST /api/doc/tasks/{task_id}/transition com { "task_status_slug": "executar-code-twoclicks" }

**Em caso de erro:**
- POST /api/doc/tasks/{task_id}/details com { "resumo": "<erro>", "prompt": null }
- POST /api/doc/tasks/{task_id}/transition com { "task_status_slug": "erro-code" }
PROMPT,
            ],

            'executar-code-twoclicks' => [
                'model'            => 'sonnet',
                'runtime_location' => 'local',
                'webhook_url'      => null,
                'code_prompt'      => <<<'PROMPT'
Você é o Code local (Sonnet). Sua tarefa é executar a implementação da task #{task_id}.

**Este status não tem execução automática por webhook. Alex aciona você manualmente.**

**Passos:**
1. Leia a task e todos os detalhes: GET /api/doc/tasks/{task_id} e GET /api/doc/tasks/{task_id}/details
2. Leia o prompt técnico gerado no status "analise-code" (último task_detail)
3. Prepare a branch:
   - git checkout develop && git pull origin develop
   - git checkout -b task-{task_id}-<slug-curto> (ou checkout se já existir)
4. Execute as mudanças conforme o prompt técnico
5. Faça commit local (sem push ainda)
6. Registre o que foi feito: POST /api/doc/tasks/{task_id}/details com { "resumo": "<o que foi implementado, arquivos modificados, decisões tomadas>", "prompt": null }
7. Transfira para revisão: POST /api/doc/tasks/{task_id}/transition com { "task_status_slug": "revisao-twoclicks" }

**Em caso de erro:**
- POST /api/doc/tasks/{task_id}/details com { "resumo": "<erro>", "prompt": null }
- POST /api/doc/tasks/{task_id}/transition com { "task_status_slug": "erro-code" }
PROMPT,
            ],

            'revisao-twoclicks' => [
                'model'            => null,
                'runtime_location' => null,
                'webhook_url'      => null,
                'code_prompt'      => null,
            ],

            'deploy-sandbox-code' => [
                'model'            => 'opus',
                'runtime_location' => 'vps',
                'webhook_url'      => $this->webhookUrl,
                'code_prompt'      => <<<'PROMPT'
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
PROMPT,
            ],

            'aprovacao-twoclicks' => [
                'model'            => null,
                'runtime_location' => null,
                'webhook_url'      => null,
                'code_prompt'      => null,
            ],

            'deploy-prod-code' => [
                'model'            => 'opus',
                'runtime_location' => 'vps',
                'webhook_url'      => $this->webhookUrl,
                'code_prompt'      => <<<'PROMPT'
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
PROMPT,
            ],

            'aprovacao-prod-twoclicks' => [
                'model'            => null,
                'runtime_location' => null,
                'webhook_url'      => null,
                'code_prompt'      => null,
                // Vestigial: deploy prod vai direto para concluido (bypass para evitar SIGTERM pós-deploy)
                'status'           => false,
            ],

            'concluido' => [
                'model'            => null,
                'runtime_location' => null,
                'webhook_url'      => null,
                'code_prompt'      => null,
            ],

            'erro-code' => [
                'model'            => null,
                'runtime_location' => null,
                'webhook_url'      => null,
                'code_prompt'      => null,
            ],
        ];
    }

    private function insertOnlyConfigs(): array
    {
        return [
            'aguardando-deploy-sandbox' => [
                'name'                 => 'Aguardando Deploy Sandbox',
                'color'                => '#F59E0B',
                'order'                => 6,
                'model'                => null,
                'runtime_location'     => null,
                'webhook_url'          => null,
                'code_prompt'          => null,
                'show_on_task'         => true,
                'auto_execute_default' => false,
            ],
            'aguardando-deploy-prod' => [
                'name'                 => 'Aguardando Deploy Prod',
                'color'                => '#F59E0B',
                'order'                => 9,
                'model'                => null,
                'runtime_location'     => null,
                'webhook_url'          => null,
                'code_prompt'          => null,
                'show_on_task'         => true,
                'auto_execute_default' => false,
            ],
        ];
    }
}
