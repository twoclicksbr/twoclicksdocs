<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TaskFaseResource;
use App\Models\TaskFase;
use Illuminate\Validation\Rule;

class TaskFaseApiController extends TaskAuxApiController
{
    protected function modelClass(): string    { return TaskFase::class; }
    protected function resourceClass(): string { return TaskFaseResource::class; }

    protected function rules(int $projectId, ?int $id = null): array
    {
        return [
            'name'   => 'required|string|max:100',
            'slug'   => ['required', 'string', 'max:50',
                Rule::unique('tc_doc.task_fases', 'slug')
                    ->where('project_id', $projectId)
                    ->whereNull('deleted_at')
                    ->ignore($id)],
            'order'  => 'nullable|integer',
            'status' => 'nullable|boolean',
        ];
    }
}
