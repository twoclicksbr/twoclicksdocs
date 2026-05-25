<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Jobs\DispatchStatusWebhookJob;
use App\Models\AuditLog;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Services\TaskAutoExecuteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

        $this->dispatchStatusWebhookIfApplicable($task);

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
        $projectId = $this->projectId($request);

        $validated = $request->validate([
            'tasks'                      => 'required|array|min:1|max:500',
            'tasks.*.title'              => 'required|string|max:255',
            'tasks.*.description'        => 'nullable|string',
            'tasks.*.task_status_id'     => ['required', 'integer',
                Rule::exists('tc_doc.task_statuses', 'id')->where('project_id', $projectId)->whereNull('deleted_at')],
            'tasks.*.task_fase_id'       => ['required', 'integer',
                Rule::exists('tc_doc.task_fases', 'id')->where('project_id', $projectId)->whereNull('deleted_at')],
            'tasks.*.task_modulo_id'     => ['required', 'integer',
                Rule::exists('tc_doc.task_modulos', 'id')->where('project_id', $projectId)->whereNull('deleted_at')],
            'tasks.*.task_tipo_id'       => ['required', 'integer',
                Rule::exists('tc_doc.task_tipos', 'id')->where('project_id', $projectId)->whereNull('deleted_at')],
            'tasks.*.task_prioridade_id' => ['required', 'integer',
                Rule::exists('tc_doc.task_prioridades', 'id')->where('project_id', $projectId)->whereNull('deleted_at')],
            'tasks.*.priority_flag'      => 'nullable|boolean',
            'tasks.*.order'              => 'nullable|integer',
        ]);
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

            // Task::insert() não dispara Eloquent events, então o
            // TaskAutoExecuteObserver não roda — aplicamos os defaults
            // manualmente em bulk pra manter o pivot sincronizado.
            app(TaskAutoExecuteService::class)
                ->applyDefaultsToTasks($tasks->pluck('id')->all(), $projectId);

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

        // Dispatch webhook fora da transaction pra evitar phantom dispatch
        // se o commit falhar. Pivot já foi populado por applyDefaultsToTasks.
        foreach ($tasks as $task) {
            $this->dispatchStatusWebhookIfApplicable($task);
        }

        return response()->json([
            'created' => $tasks->count(),
            'tasks'   => TaskResource::collection($tasks),
        ], 201);
    }

    public function bulkMoveModulo(Request $request): JsonResponse
    {
        $projectId = $this->projectId($request);

        $validated = $request->validate([
            'task_ids'       => 'required|array|min:1|max:500',
            'task_ids.*'     => 'integer',
            'task_modulo_id' => ['required', 'integer',
                Rule::exists('tc_doc.task_modulos', 'id')->where('project_id', $projectId)->whereNull('deleted_at')],
        ]);
        $taskIds    = $validated['task_ids'];
        $newModuloId = $validated['task_modulo_id'];

        // Validação de escopo: todas as task_ids devem pertencer ao projeto do token
        $validCount = Task::where('project_id', $projectId)
            ->whereIn('id', $taskIds)
            ->count();

        if ($validCount !== count($taskIds)) {
            $foundIds    = Task::where('project_id', $projectId)
                ->whereIn('id', $taskIds)
                ->pluck('id')
                ->all();
            $invalidIds  = array_values(array_diff($taskIds, $foundIds));

            return response()->json([
                'message'     => 'Uma ou mais tarefas não pertencem a este projeto.',
                'invalid_ids' => $invalidIds,
            ], 403);
        }

        $user  = $request->user();
        $token = $user?->currentAccessToken();
        $now   = now();

        $moved = DB::connection('tc_doc')->transaction(function () use ($taskIds, $projectId, $newModuloId, $user, $token, $now, $request) {
            // Captura valores antigos antes do UPDATE para o audit log
            $oldValues = Task::where('project_id', $projectId)
                ->whereIn('id', $taskIds)
                ->pluck('task_modulo_id', 'id');

            $affected = Task::where('project_id', $projectId)
                ->whereIn('id', $taskIds)
                ->update([
                    'task_modulo_id' => $newModuloId,
                    'updated_at'     => $now,
                ]);

            // Audit logs manuais — whereIn().update() não dispara AuditableObserver
            $auditRows = $oldValues->map(fn($oldModuloId, $taskId) => [
                'person_id'   => $user?->person_id,
                'project_id'  => $token?->project_id,
                'token_name'  => $token?->name,
                'action'      => 'bulk_move_modulo',
                'table_name'  => 'tasks',
                'record_id'   => $taskId,
                'old_values'  => json_encode(['task_modulo_id' => $oldModuloId]),
                'new_values'  => json_encode(['task_modulo_id' => $newModuloId]),
                'ip_address'  => $request->ip(),
                'created_at'  => $now,
            ])->values()->all();

            AuditLog::insert($auditRows);

            return $affected;
        });

        return response()->json([
            'moved'          => $moved,
            'task_modulo_id' => $newModuloId,
            'task_ids'       => $taskIds,
        ]);
    }

    public function transition(Request $request, int $task): TaskResource|JsonResponse
    {
        $projectId = $this->projectId($request);

        $validated = $request->validate([
            'task_status_id' => ['nullable', 'required_without:task_status_slug', 'integer',
                Rule::exists('tc_doc.task_statuses', 'id')
                    ->where('project_id', $projectId)
                    ->whereNull('deleted_at')],
            'task_status_slug' => ['nullable', 'required_without:task_status_id', 'string',
                Rule::exists('tc_doc.task_statuses', 'slug')
                    ->where('project_id', $projectId)
                    ->whereNull('deleted_at')],
        ]);

        $model = Task::query()
            ->with(['project:id,slug', 'autoExecuteStatuses:id'])
            ->where('project_id', $projectId)
            ->findOrFail($task);

        $newStatus = isset($validated['task_status_slug'])
            ? TaskStatus::where('project_id', $projectId)
                ->where('slug', $validated['task_status_slug'])
                ->firstOrFail()
            : TaskStatus::findOrFail($validated['task_status_id']);

        $model->update(['task_status_id' => $newStatus->id]);

        $this->dispatchStatusWebhookIfApplicable($model);

        return new TaskResource($model->fresh());
    }

    private function dispatchStatusWebhookIfApplicable(Task $task): void
    {
        $task->loadMissing(['autoExecuteStatuses:id', 'project:id,slug', 'status']);
        $status = $task->getStatusRelation();

        if (! $status || ! $status->webhook_url) {
            return;
        }

        if (! $task->autoExecuteStatuses->contains('id', $status->id)) {
            return;
        }

        DispatchStatusWebhookJob::dispatch(
            $task->id,
            $status->id,
            $status->webhook_url,
            $status->slug,
            $task->project?->slug,
        );
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
