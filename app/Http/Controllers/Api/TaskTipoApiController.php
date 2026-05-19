<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TaskTipoResource;
use App\Models\TaskTipo;
use Illuminate\Validation\Rule;

class TaskTipoApiController extends TaskAuxApiController
{
    protected function modelClass(): string    { return TaskTipo::class; }
    protected function resourceClass(): string { return TaskTipoResource::class; }

    protected function rules(int $projectId, ?int $id = null): array
    {
        return [
            'name'   => 'required|string|max:50',
            'slug'   => ['required', 'string', 'max:50',
                Rule::unique('tc_doc.task_tipos', 'slug')
                    ->where('project_id', $projectId)
                    ->whereNull('deleted_at')
                    ->ignore($id)],
            'order'  => 'nullable|integer',
            'status' => 'nullable|boolean',
        ];
    }
}
