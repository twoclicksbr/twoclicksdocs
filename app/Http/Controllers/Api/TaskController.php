<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\AuditLog;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function bulkStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tasks'                          => 'required|array|min:1|max:500',
            'tasks.*.title'                  => 'required|string|max:255',
            'tasks.*.description'            => 'nullable|string',
            'tasks.*.task_status_id'         => 'required|integer|exists:tc_doc.task_statuses,id',
            'tasks.*.task_fase_id'           => 'required|integer|exists:tc_doc.task_fases,id',
            'tasks.*.task_modulo_id'         => 'required|integer|exists:tc_doc.task_modulos,id',
            'tasks.*.task_tipo_id'           => 'required|integer|exists:tc_doc.task_tipos,id',
            'tasks.*.task_prioridade_id'     => 'required|integer|exists:tc_doc.task_prioridades,id',
            'tasks.*.priority_flag'          => 'nullable|boolean',
            'tasks.*.order'                  => 'nullable|integer',
        ]);

        $projectId = $this->projectId($request);
        $user      = $request->user();
        $token     = $user?->currentAccessToken();
        $now       = now();

        $tasks = DB::connection('tc_doc')->transaction(function () use ($validated, $projectId, $user, $token, $now, $request) {
            $rows = collect($validated['tasks'])->map(fn($t) => [
                'project_id'         => $projectId,
                'title'              => $t['title'],
                'description'        => $t['description'] ?? null,
                'task_status_id'     => $t['task_status_id'],
                'task_fase_id'       => $t['task_fase_id'],
                'task_modulo_id'     => $t['task_modulo_id'],
                'task_tipo_id'       => $t['task_tipo_id'],
                'task_prioridade_id' => $t['task_prioridade_id'],
                'priority_flag'      => $t['priority_flag'] ?? false,
                'order'              => $t['order'] ?? 0,
                'status'             => true,
                'created_at'         => $now,
                'updated_at'         => $now,
            ])->all();

            Task::insert($rows);

            $tasks = Task::where('project_id', $projectId)
                ->where('created_at', $now)
                ->orderBy('id')
                ->get();

            // Audit logs manuais — Task::insert() não dispara AuditableObserver
            $sanitize = fn(?array $v) => empty($v) ? null : collect($v)
                ->except(['password', 'remember_token', 'token'])
                ->all();

            $auditRows = $tasks->map(fn($task) => [
                'person_id'   => $user?->person_id,
                'project_id'  => $token?->project_id,
                'token_name'  => $token?->name,
                'action'      => 'create',
                'table_name'  => 'tasks',
                'record_id'   => $task->id,
                'old_values'  => null,
                'new_values'  => json_encode($sanitize($task->getAttributes())),
                'ip_address'  => $request->ip(),
                'created_at'  => $now,
            ])->all();

            AuditLog::insert($auditRows);

            return $tasks;
        });

        return response()->json([
            'created' => $tasks->count(),
            'tasks'   => TaskResource::collection($tasks),
        ], 201);
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
