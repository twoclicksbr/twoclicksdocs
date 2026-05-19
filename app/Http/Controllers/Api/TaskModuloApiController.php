<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TaskModuloResource;
use App\Models\TaskModulo;
use Illuminate\Validation\Rule;

class TaskModuloApiController extends TaskAuxApiController
{
    protected function modelClass(): string    { return TaskModulo::class; }
    protected function resourceClass(): string { return TaskModuloResource::class; }

    protected function rules(int $projectId, ?int $id = null): array
    {
        return [
            'name'   => 'required|string|max:50',
            'slug'   => ['required', 'string', 'max:50',
                Rule::unique('tc_doc.task_modulos', 'slug')
                    ->where('project_id', $projectId)
                    ->whereNull('deleted_at')
                    ->ignore($id)],
            'order'  => 'nullable|integer',
            'status' => 'nullable|boolean',
        ];
    }
}
