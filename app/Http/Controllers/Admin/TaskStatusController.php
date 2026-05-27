<?php

namespace App\Http\Controllers\Admin;

use App\Models\TaskStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskStatusController extends ProjectScopedCrudController
{
    protected string $model = TaskStatus::class;
    protected string $route = 'admin.task-statuses';
    protected string $title = 'Status';
    protected string $titlePlural = 'Status de Tarefa';
    protected array $searchable = ['name', 'slug'];
    protected string $orderBy = 'order';
    protected array $fields = [
        ['name' => 'name',                 'label' => 'Nome',                       'type' => 'text'],
        ['name' => 'slug',                 'label' => 'Slug',                       'type' => 'text'],
        ['name' => 'color',                'label' => 'Cor (hex)',                  'type' => 'text',          'in_table' => false],
        ['name' => 'order',                'label' => 'Ordem',                      'type' => 'number'],
        ['name' => '_execute',             'label' => 'Executar',                   'type' => 'execute_badge', 'in_form' => false],
        ['name' => 'model',                'label' => 'Modelo (LLM)',               'type' => 'select'],
        ['name' => 'status',               'label' => 'Ativo',                      'type' => 'boolean'],
        ['name' => 'show_on_task',         'label' => 'Mostrar no form da Task',    'type' => 'boolean',       'in_table' => false],
        ['name' => 'auto_execute_default', 'label' => 'Auto-executar (default)',    'type' => 'boolean',       'in_table' => false],
        ['name' => 'runtime_location',     'label' => 'Local de Execução',          'type' => 'select',        'in_table' => false],
        ['name' => 'webhook_url',          'label' => 'Webhook URL',                'type' => 'url',           'in_table' => false],
        ['name' => 'executor_type',        'label' => 'Tipo de Executor',           'type' => 'select',        'in_table' => false],
        ['name' => 'code_prompt',          'label' => 'Prompt do Code / Shell Script', 'type' => 'textarea',  'rows' => 12, 'in_table' => false],
    ];

    protected function options(): array
    {
        return [
            'model'            => ['' => '— nenhum —', 'opus' => 'Opus', 'sonnet' => 'Sonnet'],
            'runtime_location' => ['' => '— nenhum —', 'vps' => 'VPS', 'local' => 'Local'],
            'executor_type'    => ['code' => 'Code (LLM)', 'shell' => 'Shell (bash)'],
        ];
    }

    protected function prepareData(Request $request, array $data): array
    {
        $data = parent::prepareData($request, $data);
        foreach (['model', 'runtime_location', 'webhook_url', 'code_prompt'] as $field) {
            if (! array_key_exists($field, $data)) {
                $data[$field] = null;
            }
        }
        return $data;
    }

    protected function rules(?int $id = null): array
    {
        return [
            'name'                 => 'required|string|max:50',
            'slug'                 => ['required', 'string', 'max:50',
                Rule::unique('tc_doc.task_statuses', 'slug')
                    ->where('project_id', $this->currentProjectId)
                    ->ignore($id)],
            'color'                => 'nullable|string|max:20',
            'order'                => 'nullable|integer',
            'show_on_task'         => 'nullable|boolean',
            'auto_execute_default' => 'nullable|boolean',
            'status'               => 'nullable|boolean',
            'model'                => 'nullable|in:opus,sonnet',
            'runtime_location'     => 'nullable|in:vps,local',
            'webhook_url'          => 'nullable|url|max:500',
            'code_prompt'          => 'nullable|string',
            'executor_type'        => 'nullable|in:code,shell',
        ];
    }
}
