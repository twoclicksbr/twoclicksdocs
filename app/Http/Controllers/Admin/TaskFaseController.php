<?php

namespace App\Http\Controllers\Admin;

use App\Models\TaskFase;
use Illuminate\Validation\Rule;

class TaskFaseController extends CrudController
{
    protected string $model = TaskFase::class;
    protected string $route = 'admin.task-fases';
    protected string $title = 'Fase';
    protected string $titlePlural = 'Fases';
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
            'name'   => 'required|string|max:100',
            'slug'   => ['required', 'string', 'max:50', Rule::unique('tc_doc.task_fases', 'slug')->ignore($id)],
            'order'  => 'nullable|integer',
            'status' => 'nullable|boolean',
        ];
    }
}
