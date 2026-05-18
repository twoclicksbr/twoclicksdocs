<?php

namespace App\Http\Controllers\Admin;

use App\Models\TaskStatus;

class TaskStatusController extends CrudController
{
    protected string $model = TaskStatus::class;
    protected string $route = 'admin.task-statuses';
    protected string $title = 'Status';
    protected string $titlePlural = 'Status de Tarefa';
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
            'name'   => 'required|string|max:50',
            'slug'   => "required|string|max:50|unique:tc_doc.task_statuses,slug,{$id}",
            'color'  => 'nullable|string|max:20',
            'order'  => 'nullable|integer',
            'status' => 'nullable|boolean',
        ];
    }
}
