<?php

namespace App\Http\Controllers\Admin;

use App\Models\TaskPrioridade;
use Illuminate\Validation\Rule;

class TaskPrioridadeController extends ProjectScopedCrudController
{
    protected string $model = TaskPrioridade::class;
    protected string $route = 'admin.task-prioridades';
    protected string $title = 'Prioridade';
    protected string $titlePlural = 'Prioridades';
    protected array $searchable = ['name', 'slug'];
    protected string $orderBy = 'order';
    protected array $fields = [
        ['name' => 'name',   'label' => 'Nome',      'type' => 'text'],
        ['name' => 'slug',   'label' => 'Slug',      'type' => 'text'],
        ['name' => 'color',  'label' => 'Cor (hex)', 'type' => 'text'],
        ['name' => 'order',  'label' => 'Ordem',     'type' => 'number'],
        ['name' => 'status', 'label' => 'Ativo',     'type' => 'boolean'],
    ];

    protected function rules(?int $id = null): array
    {
        return [
            'name'   => 'required|string|max:20',
            'slug'   => ['required', 'string', 'max:20',
                Rule::unique('tc_doc.task_prioridades', 'slug')
                    ->where('project_id', $this->currentProjectId)
                    ->ignore($id)],
            'color'  => 'nullable|string|max:20',
            'order'  => 'nullable|integer',
            'status' => 'nullable|boolean',
        ];
    }
}
