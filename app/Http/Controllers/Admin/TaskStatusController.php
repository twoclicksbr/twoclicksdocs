<?php

namespace App\Http\Controllers\Admin;

use App\Models\TaskStatus;
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
        ['name' => 'color',                'label' => 'Cor (hex)',                  'type' => 'text'],
        ['name' => 'order',                'label' => 'Ordem',                      'type' => 'number'],
        ['name' => 'show_on_task',         'label' => 'Mostrar no form da Task',    'type' => 'boolean'],
        ['name' => 'auto_execute_default', 'label' => 'Auto-executar (default)',    'type' => 'boolean'],
        ['name' => 'status',               'label' => 'Ativo',                      'type' => 'boolean'],
    ];

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
        ];
    }
}
