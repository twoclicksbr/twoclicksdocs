<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TaskPrioridadeResource;
use App\Models\TaskPrioridade;
use Illuminate\Validation\Rule;

class TaskPrioridadeApiController extends TaskAuxApiController
{
    protected function modelClass(): string    { return TaskPrioridade::class; }
    protected function resourceClass(): string { return TaskPrioridadeResource::class; }

    protected function rules(int $projectId, ?int $id = null): array
    {
        return [
            'name'   => 'required|string|max:20',
            'slug'   => ['required', 'string', 'max:20',
                Rule::unique('tc_doc.task_prioridades', 'slug')
                    ->where('project_id', $projectId)
                    ->whereNull('deleted_at')
                    ->ignore($id)],
            'color'  => 'nullable|string|max:20',
            'order'  => 'nullable|integer',
            'status' => 'nullable|boolean',
        ];
    }
}
