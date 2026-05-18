<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\TaskDetail\StoreTaskDetailRequest;
use App\Http\Requests\TaskDetail\UpdateTaskDetailRequest;
use App\Http\Resources\TaskDetailResource;
use App\Models\Task;
use App\Models\TaskDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskDetailController extends ApiController
{
    /**
     * Garante que a task pertence ao projeto do token.
     */
    private function findTaskOrFail(Request $request, int $taskId): Task
    {
        return Task::query()
            ->where('project_id', $this->projectId($request))
            ->findOrFail($taskId);
    }

    public function index(Request $request, int $task)
    {
        $this->findTaskOrFail($request, $task);

        $query = TaskDetail::query()
            ->where('task_id', $task)
            ->expand($request);

        $this->applyFilters($query, $request, ['task_status_id', 'person_id']);
        $this->applySearch($query, $request, ['prompt', 'resumo']);
        $this->applyOrder($query, $request, 'created_at,asc');

        return TaskDetailResource::collection(
            $query->paginate($this->perPage($request))
        );
    }

    public function store(StoreTaskDetailRequest $request, int $task): JsonResponse
    {
        $this->findTaskOrFail($request, $task);

        $detail = TaskDetail::create(array_merge(
            $request->validated(),
            ['task_id' => $task]
        ));

        return (new TaskDetailResource($detail))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $task, int $detail): TaskDetailResource
    {
        $this->findTaskOrFail($request, $task);

        $model = TaskDetail::query()
            ->where('task_id', $task)
            ->expand($request)
            ->findOrFail($detail);

        return new TaskDetailResource($model);
    }

    public function update(UpdateTaskDetailRequest $request, int $task, int $detail): TaskDetailResource
    {
        $this->findTaskOrFail($request, $task);

        $model = TaskDetail::query()
            ->where('task_id', $task)
            ->findOrFail($detail);

        $model->update($request->validated());

        return new TaskDetailResource($model->fresh());
    }

    public function destroy(Request $request, int $task, int $detail): JsonResponse
    {
        $this->findTaskOrFail($request, $task);

        $model = TaskDetail::query()
            ->where('task_id', $task)
            ->findOrFail($detail);

        $model->delete();

        return response()->json([
            'message' => 'Detalhe removido.',
        ]);
    }
}
