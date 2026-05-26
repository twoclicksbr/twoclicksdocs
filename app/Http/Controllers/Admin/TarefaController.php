<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTarefaRequest;
use App\Http\Requests\Admin\UpdateTarefaRequest;
use App\Models\Task;
use App\Models\TaskDetail;
use App\Models\TaskFase;
use App\Models\TaskModulo;
use App\Models\TaskPrioridade;
use App\Models\TaskStatus;
use App\Models\TaskTipo;
use App\Services\ProjectContext;
use App\Services\TaskAutoExecuteService;
use Illuminate\Http\Request;

class TarefaController extends Controller
{
    public function index(Request $request)
    {
        $projectId    = ProjectContext::currentId();
        $statuses     = TaskStatus::where('project_id', $projectId)->orderBy('order')->get();
        $statusId     = $request->query('task_status_id');
        $priorityOnly = $request->query('priority_flag') === 'true';

        $allowedSort = ['id', 'title', 'task_status_id', 'task_fase_id', 'task_modulo_id', 'task_prioridade_id', 'priority_flag', 'order'];
        $sortField   = in_array($request->query('sort'), $allowedSort) ? $request->query('sort') : 'order';
        $sortDir     = in_array($request->query('dir'), ['asc', 'desc']) ? $request->query('dir') : 'asc';

        $q = Task::with(['status', 'fase', 'modulo', 'tipo', 'prioridade'])
            ->where('project_id', $projectId);
        if ($statusId)     $q->where('task_status_id', $statusId);
        if ($priorityOnly) $q->where('priority_flag', true);
        $q->orderBy($sortField, $sortDir);
        if ($sortField !== 'order') $q->orderBy('order', 'asc');
        $tasks = $q->get();

        return view('admin.tarefas.index', compact(
            'statuses', 'statusId', 'tasks', 'priorityOnly', 'sortField', 'sortDir'
        ));
    }

    public function create()
    {
        $projectId = ProjectContext::currentId();
        $aux       = $this->loadAux($projectId);

        return view('admin.tarefas.create', compact('aux'));
    }

    public function store(StoreTarefaRequest $request)
    {
        $projectId = ProjectContext::currentId();
        $data = $request->validated();
        $data['project_id']    = $projectId;
        $data['priority_flag'] = $request->boolean('priority_flag');
        $data['status']        = $request->boolean('status', true);

        $task = Task::create($data);

        $this->syncAutoExecuteStatuses($task, $request, $projectId);

        return redirect()
            ->route('admin.tarefas.index')
            ->with('success', 'Tarefa criada com sucesso.');
    }

    public function show($id)
    {
        $task = Task::with([
            'project', 'status', 'fase', 'modulo', 'tipo', 'prioridade', 'autoExecuteStatuses',
        ])->findOrFail($id);

        if ($task->project_id !== ProjectContext::currentId()) {
            abort(404);
        }

        $details = TaskDetail::with(['status', 'person'])
            ->where('task_id', $task->id)
            ->orderBy('started_at', 'desc')
            ->get();

        $allStatuses = TaskStatus::where('project_id', $task->project_id)
            ->orderBy('order')
            ->get(['id', 'name', 'slug', 'show_on_task', 'order']);

        return view('admin.tarefas.show', compact('task', 'details', 'allStatuses'));
    }

    public function edit($id)
    {
        $task = Task::with('autoExecuteStatuses:id')->findOrFail($id);

        if ($task->project_id !== ProjectContext::currentId()) {
            abort(404);
        }

        $aux = $this->loadAux($task->project_id);

        return view('admin.tarefas.edit', compact('task', 'aux'));
    }

    public function update(UpdateTarefaRequest $request, $id)
    {
        $task = Task::findOrFail($id);

        if ($task->project_id !== ProjectContext::currentId()) {
            abort(404);
        }

        $data = $request->validated();
        unset($data['project_id']);
        $data['priority_flag'] = $request->boolean('priority_flag');
        $data['status']        = $request->boolean('status');

        $task->update($data);

        $this->syncAutoExecuteStatuses($task, $request, $task->project_id);

        return redirect()
            ->route('admin.tarefas.show', $task->id)
            ->with('success', 'Tarefa atualizada com sucesso.');
    }

    /**
     * Sincroniza task_auto_execute_statuses com a seleção do form (checkboxes
     * dos statuses com show_on_task=true) + os "sempre auto"
     * (show_on_task=false E auto_execute_default=true).
     */
    private function syncAutoExecuteStatuses(Task $task, Request $request, int $projectId): void
    {
        $selectedFromForm = collect($request->input('auto_execute_status_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        $alwaysAuto = app(TaskAutoExecuteService::class)->defaultStatusIdsFor($projectId);

        $allValidIds = TaskStatus::where('project_id', $projectId)->pluck('id')->all();

        $merged = array_values(array_unique(array_intersect(
            array_merge($selectedFromForm, $alwaysAuto),
            $allValidIds,
        )));

        $task->autoExecuteStatuses()->sync($merged);
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);

        if ($task->project_id !== ProjectContext::currentId()) {
            abort(404);
        }

        $detailsCount = $task->details()->count();
        $task->delete();

        $msg = 'Tarefa excluída.';
        if ($detailsCount > 0) {
            $msg .= " {$detailsCount} ciclo(s) de execução preservado(s).";
        }

        return redirect()
            ->route('admin.tarefas.index')
            ->with('success', $msg);
    }

    public function auxiliares(int $id)
    {
        return response()->json([
            'statuses'    => TaskStatus::where('project_id', $id)->orderBy('order')->get(['id', 'name']),
            'fases'       => TaskFase::where('project_id', $id)->orderBy('order')->get(['id', 'name']),
            'modulos'     => TaskModulo::where('project_id', $id)->orderBy('order')->get(['id', 'name']),
            'tipos'       => TaskTipo::where('project_id', $id)->orderBy('order')->get(['id', 'name']),
            'prioridades' => TaskPrioridade::where('project_id', $id)->orderBy('order')->get(['id', 'name']),
        ]);
    }

    private function loadAux(int $projectId): array
    {
        return [
            'statuses'    => TaskStatus::where('project_id', $projectId)->orderBy('order')->get(),
            'fases'       => TaskFase::where('project_id', $projectId)->orderBy('order')->get(),
            'modulos'     => TaskModulo::where('project_id', $projectId)->orderBy('order')->get(),
            'tipos'       => TaskTipo::where('project_id', $projectId)->orderBy('order')->get(),
            'prioridades' => TaskPrioridade::where('project_id', $projectId)->orderBy('order')->get(),
        ];
    }
}
