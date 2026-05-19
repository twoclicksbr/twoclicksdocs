<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends ApiController
{
    public function index(Request $request)
    {
        $query = Task::query()
            ->where('project_id', $this->projectId($request))
            ->expand($request);

        $this->applyFilters($query, $request, [
            'status',
            'task_status_id',
            'task_fase_id',
            'task_modulo_id',
            'task_tipo_id',
            'task_prioridade_id',
            'priority_flag',
        ]);
        $this->applySearch($query, $request, ['title', 'description']);
        $this->applyOrder($query, $request, 'order,asc');

        return TaskResource::collection(
            $query->paginate($this->perPage($request))
        );
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = Task::create(array_merge(
            $request->validated(),
            ['project_id' => $this->projectId($request)]
        ));

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $task): TaskResource
    {
        $model = Task::query()
            ->where('project_id', $this->projectId($request))
            ->expand($request)
            ->findOrFail($task);

        return new TaskResource($model);
    }

    public function update(UpdateTaskRequest $request, int $task): TaskResource
    {
        $model = Task::query()
            ->where('project_id', $this->projectId($request))
            ->findOrFail($task);

        $model->update($request->validated());

        return new TaskResource($model->fresh());
    }

    public function destroy(Request $request, int $task): JsonResponse
    {
        $model = Task::query()
            ->where('project_id', $this->projectId($request))
            ->findOrFail($task);

        $model->delete();

        return response()->json([
            'message' => 'Tarefa removida.',
        ]);
    }
}
