<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Cria os 7 statuses base do fluxo v2 no projeto `docstwoclicks`
 * (sandbox). Idempotente via updateOrInsert por (project_id, slug).
 *
 * Origem: task #70 (follow-up da #19/task_detail #76). O sandbox tem
 * apenas 3 statuses históricos (deploy-sandbox-code, deploy-prod-code,
 * aprovacao-prod-twoclicks). Os 7 statuses base do fluxo v2 nunca foram
 * inseridos lá, o que impedia tasks de nascer em `fazer-code` no sandbox.
 *
 * `webhook_url` é resolvido via config('app.url') — no sandbox aponta
 * pra https://api.sandbox.twoclicks.com.br/api/webhook/code, evitando
 * que webhooks do sandbox disparem em produção.
 *
 * NÃO incluído em DatabaseSeeder.php. Rodar manualmente:
 *
 *     php artisan db:seed --class=TaskStatusV2BaseSeeder
 *
 * Guard contra environment=production por segurança extra.
 */
class TaskStatusV2BaseSeeder extends Seeder
{
    private string $projectSlug = 'docstwoclicks';

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->warn('TaskStatusV2BaseSeeder: skip em production por segurança.');
            return;
        }

        $projectId = DB::connection('tc_doc')
            ->table('projects')
            ->where('slug', $this->projectSlug)
            ->value('id');

        if (! $projectId) {
            $this->command->warn("TaskStatusV2BaseSeeder: projeto '{$this->projectSlug}' não encontrado.");
            return;
        }

        $webhookUrl = rtrim((string) config('app.url'), '/') . '/api/webhook/code';
        $now        = now();
        $statuses   = $this->configs($webhookUrl);

        $inserted = 0;
        $updated  = 0;

        foreach ($statuses as $row) {
            $exists = DB::connection('tc_doc')
                ->table('task_statuses')
                ->where('project_id', $projectId)
                ->where('slug', $row['slug'])
                ->exists();

            DB::connection('tc_doc')
                ->table('task_statuses')
                ->updateOrInsert(
                    ['project_id' => $projectId, 'slug' => $row['slug']],
                    array_merge($row, [
                        'project_id' => $projectId,
                        'status'     => true,
                        'updated_at' => $now,
                    ], $exists ? [] : ['created_at' => $now]),
                );

            $exists ? $updated++ : $inserted++;
        }

        $this->command->info(
            "TaskStatusV2BaseSeeder: projeto '{$this->projectSlug}' (id={$projectId}) — "
            . "inseridos={$inserted}, atualizados={$updated} (de " . count($statuses) . " statuses base)."
        );
        $this->command->info("webhook_url usado: {$webhookUrl}");
    }

    private function configs(string $webhookUrl): array
    {
        return [
            [
                'slug'                 => 'fazer-code',
                'name'                 => 'Fazer - Code',
                'order'                => 1,
                'model'                => 'opus',
                'runtime_location'     => 'vps',
                'webhook_url'          => $webhookUrl,
                'show_on_task'         => false,
                'auto_execute_default' => true,
                'code_prompt'          => $this->promptFazerCode(),
            ],
            [
                'slug'                 => 'analise-code',
                'name'                 => 'Análise - Code',
                'order'                => 2,
                'model'                => 'opus',
                'runtime_location'     => 'vps',
                'webhook_url'          => $webhookUrl,
                'show_on_task'         => false,
                'auto_execute_default' => true,
                'code_prompt'          => $this->promptAnaliseCode(),
            ],
            [
                'slug'                 => 'executar-code-twoclicks',
                'name'                 => 'Executar - Code/TwoClicks',
                'order'                => 3,
                'model'                => 'sonnet',
                'runtime_location'     => 'local',
                'webhook_url'          => null,
                'show_on_task'         => true,
                'auto_execute_default' => false,
                'code_prompt'          => $this->promptExecutarCode(),
            ],
            [
                'slug'                 => 'revisao-twoclicks',
                'name'                 => 'Revisão - TwoClicks',
                'order'                => 4,
                'model'                => null,
                'runtime_location'     => null,
                'webhook_url'          => null,
                'show_on_task'         => false,
                'auto_execute_default' => false,
                'code_prompt'          => null,
            ],
            [
                'slug'                 => 'aprovacao-twoclicks',
                'name'                 => 'Aprovação - TwoClicks',
                'order'                => 6,
                'model'                => null,
                'runtime_location'     => null,
                'webhook_url'          => null,
                'show_on_task'         => false,
                'auto_execute_default' => false,
                'code_prompt'          => null,
            ],
            [
                'slug'                 => 'concluido',
                'name'                 => 'Concluído',
                'order'                => 9,
                'model'                => null,
                'runtime_location'     => null,
                'webhook_url'          => null,
                'show_on_task'         => false,
                'auto_execute_default' => false,
                'code_prompt'          => null,
            ],
            [
                'slug'                 => 'cancelado',
                'name'                 => 'Cancelado',
                'order'                => 98,
                'model'                => null,
                'runtime_location'     => null,
                'webhook_url'          => null,
                'show_on_task'         => false,
                'auto_execute_default' => false,
                'code_prompt'          => null,
            ],
            [
                'slug'                 => 'erro-code',
                'name'                 => 'Erro - Code',
                'order'                => 99,
                'model'                => null,
                'runtime_location'     => null,
                'webhook_url'          => null,
                'show_on_task'         => false,
                'auto_execute_default' => false,
                'code_prompt'          => null,
            ],
        ];
    }

    private function promptFazerCode(): string
    {
        return <<<'PROMPT'
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
PROMPT;
    }

    private function promptAnaliseCode(): string
    {
        return <<<'PROMPT'
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
PROMPT;
    }

    private function promptExecutarCode(): string
    {
        return <<<'PROMPT'
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
PROMPT;
    }
}
