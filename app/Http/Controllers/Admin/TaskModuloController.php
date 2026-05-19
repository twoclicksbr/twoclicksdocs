<?php

namespace App\Http\Controllers\Admin;

use App\Models\TaskModulo;
use Illuminate\Validation\Rule;

class TaskModuloController extends ProjectScopedCrudController
{
    protected string $model = TaskModulo::class;
    protected string $route = 'admin.task-modulos';
    protected string $title = 'Módulo';
    protected string $titlePlural = 'Módulos';
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
            'slug'   => ['required', 'string', 'max:50',
                Rule::unique('tc_doc.task_modulos', 'slug')
                    ->where('project_id', $this->currentProjectId)
                    ->ignore($id)],
            'order'  => 'nullable|integer',
            'status' => 'nullable|boolean',
        ];
    }
}
