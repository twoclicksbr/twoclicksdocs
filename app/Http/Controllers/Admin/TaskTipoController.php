<?php

namespace App\Http\Controllers\Admin;

use App\Models\TaskTipo;
use Illuminate\Validation\Rule;

class TaskTipoController extends CrudController
{
    protected string $model = TaskTipo::class;
    protected string $route = 'admin.task-tipos';
    protected string $title = 'Tipo';
    protected string $titlePlural = 'Tipos';
    protected array $searchable = ['name', 'slug'];
    protected string $orderBy = 'order';
    protected array $fields = [
        ['name' => 'name',   'label' => 'Nome',  'type' => 'text'],
        ['name' => 'slug',   'label' => 'Slug',  'type' => 'text'],
        ['name' => 'order',  'label' => 'Ordem', 'type' => 'number'],
        ['name' => 'status', 'label' => 'Ativo', 'type' => 'boolean'],
    ];

    protected function rules(?int $id = null): array
    {
        return [
            'name'   => 'required|string|max:50',
            'slug'   => ['required', 'string', 'max:50', Rule::unique('tc_doc.task_tipos', 'slug')->ignore($id)],
            'order'  => 'nullable|integer',
            'status' => 'nullable|boolean',
        ];
    }
}
