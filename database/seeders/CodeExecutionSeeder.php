<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\TaskStatus;
use Illuminate\Database\Seeder;

class CodeExecutionSeeder extends Seeder
{
    private string $webhookBase;

    public function __construct()
    {
        $this->webhookBase = rtrim(config('app.url'), '/') . '/api/webhook/code';
    }

    // model + runtime_location + webhook_url + code_prompt por slug
    private function statusConfig(): array
    {
        return [
            'fazer-code' => [
                'model'            => null,
                'runtime_location' => null,
                'webhook_url'      => null,
                'code_prompt'      => null,
            ],
            'analise-code' => [
                'model'            => 'opus',
                'runtime_location' => 'vps',
                'webhook_url'      => $this->webhookBase,
                'code_prompt'      => <<<'PROMPT'
Você é Code, executor autônomo do sistema TwoClicks. Uma tarefa foi atribuída a você para análise.

1. Leia a tarefa: GET /doc/tasks/{task_id}?expand=status
2. Verifique idempotência: se task_status.slug não for "analise-code", encerre sem ação.
3. Leia os documentos de arquitetura relevantes ao escopo da tarefa.
4. Crie task_details documentando:
   - Entendimento do requisito
   - Arquivos afetados (migrations, models, controllers, rotas, seeders, testes)
   - Plano de execução passo a passo
   - Riscos e decisões técnicas
5. Transicione para "executar-code" via transition_task.

Em caso de erro irrecuperável: crie task_detail descrevendo o erro em detalhes, depois transicione para "erro-code" via transition_task. Não tente corrigir indefinidamente — falhe rapidamente e deixe o usuário decidir.
PROMPT,
            ],
            'executar-code' => [
                'model'            => 'sonnet',
                'runtime_location' => 'local',
                'webhook_url'      => null, // acionado manualmente
                'code_prompt'      => <<<'PROMPT'
Você é Code, executor autônomo do sistema TwoClicks. Uma tarefa está pronta para execução.

1. Leia a tarefa: GET /doc/tasks/{task_id}?expand=status
2. Verifique idempotência: se task_status.slug não for "executar-code", encerre sem ação.
3. Leia o plano criado na fase de análise (task_details).
4. Execute o plano: crie/edite migrations, models, controllers, rotas, seeders, testes conforme necessário.
5. Rode os testes e verifique que nada quebrou.
6. Crie task_detail com resumo do que foi implementado.
7. Transicione para "revisao-code" via transition_task.

Em caso de erro irrecuperável: crie task_detail descrevendo o erro em detalhes, depois transicione para "erro-code" via transition_task. Não tente corrigir indefinidamente — falhe rapidamente e deixe o usuário decidir.
PROMPT,
            ],
            'revisao-code' => [
                'model'            => 'sonnet',
                'runtime_location' => 'vps',
                'webhook_url'      => $this->webhookBase,
                'code_prompt'      => <<<'PROMPT'
Você é Code, executor autônomo do sistema TwoClicks. Uma tarefa foi implementada e aguarda revisão.

1. Leia a tarefa: GET /doc/tasks/{task_id}?expand=status
2. Verifique idempotência: se task_status.slug não for "revisao-code", encerre sem ação.
3. Leia os task_details da fase de execução.
4. Verifique:
   - A implementação atende ao requisito original?
   - Existem edge cases não tratados?
   - O código segue os padrões do projeto?
   - Migrations, seeders e rotas estão corretos?
5. Crie task_detail com o resultado da revisão (aprovado ou pendências encontradas).
6. Se aprovado → transicione para "aprovacao-two-clicks" via transition_task.
7. Se pendências → transicione para "executar-code" via transition_task com as pendências no task_detail.

Em caso de erro irrecuperável: crie task_detail descrevendo o erro, depois transicione para "erro-code" via transition_task.
PROMPT,
            ],
            'aprovacao-two-clicks' => [
                'model'            => null,
                'runtime_location' => null,
                'webhook_url'      => null,
                'code_prompt'      => null,
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

    public function run(): void
    {
        $config = $this->statusConfig();
        $projects = Project::all();

        foreach ($projects as $project) {
            // Criar erro-code se não existir
            $maxOrder = TaskStatus::where('project_id', $project->id)->max('order') ?? 6;

            TaskStatus::firstOrCreate(
                ['project_id' => $project->id, 'slug' => 'erro-code'],
                [
                    'name'             => 'Erro - Code',
                    'slug'             => 'erro-code',
                    'color'            => '#DC2626',
                    'model'            => null,
                    'runtime_location' => null,
                    'webhook_url'      => null,
                    'code_prompt'      => null,
                    'order'            => $maxOrder + 1,
                    'status'           => true,
                ]
            );

            // Popular campos nos status existentes
            foreach ($config as $slug => $fields) {
                TaskStatus::where('project_id', $project->id)
                    ->where('slug', $slug)
                    ->update($fields);
            }
        }

        $this->command->info('CodeExecutionSeeder: erro-code criado e campos populados em ' . $projects->count() . ' projetos.');
    }
}
