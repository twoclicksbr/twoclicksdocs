<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TaskStatusResource;
use App\Models\TaskStatus;
use Illuminate\Validation\Rule;

class TaskStatusApiController extends TaskAuxApiController
{
    protected function modelClass(): string    { return TaskStatus::class; }
    protected function resourceClass(): string { return TaskStatusResource::class; }

    protected function rules(int $projectId, ?int $id = null): array
    {
        return [
            'name'                 => 'required|string|max:50',
            'slug'                 => ['required', 'string', 'max:50',
                Rule::unique('tc_doc.task_statuses', 'slug')
                    ->where('project_id', $projectId)
                    ->whereNull('deleted_at')
                    ->ignore($id)],
            'color'                => 'nullable|string|max:20',
            'order'                => 'nullable|integer',
            'status'               => 'nullable|boolean',
            'model'                => 'nullable|in:opus,sonnet',
            'runtime_location'     => 'nullable|in:vps,local',
            'webhook_url'          => 'nullable|url|max:500',
            'code_prompt'          => 'nullable|string',
            'show_on_task'         => 'nullable|boolean',
            'auto_execute_default' => 'nullable|boolean',
        ];
    }
}
