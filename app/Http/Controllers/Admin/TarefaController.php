<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTarefaRequest;
use App\Http\Requests\Admin\UpdateTarefaRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDetail;
use App\Models\TaskFase;
use App\Models\TaskModulo;
use App\Models\TaskPrioridade;
use App\Models\TaskStatus;
use App\Models\TaskTipo;
use Illuminate\Http\Request;

class TarefaController extends Controller
{
    public function index(Request $request)
    {
        $projects  = Project::orderBy('name')->get();
        $projectId = $request->query('project_id', $projects->first()?->id);
        $statuses  = TaskStatus::where('project_id', $projectId)->orderBy('order')->get();
        $statusId     = $request->query('task_status_id');
        $priorityOnly = $request->query('priority_flag') === 'true';

        $allowedSort = ['id', 'title', 'task_status_id', 'task_fase_id', 'task_modulo_id', 'task_prioridade_id', 'priority_flag', 'order'];
        $sortField   = in_array($request->query('sort'), $allowedSort) ? $request->query('sort') : 'order';
        $sortDir     = in_array($request->query('dir'), ['asc', 'desc']) ? $request->query('dir') : 'asc';

        $tasks = collect();
        if ($projectId) {
            $q = Task::with(['status', 'fase', 'modulo', 'tipo', 'prioridade'])
                ->where('project_id', $projectId);
            if ($statusId)     $q->where('task_status_id', $statusId);
            if ($priorityOnly) $q->where('priority_flag', true);
            $q->orderBy($sortField, $sortDir);
            if ($sortField !== 'order') $q->orderBy('order', 'asc');
            $tasks = $q->get();
        }

        return view('admin.tarefas.index', compact(
            'projects', 'statuses', 'projectId', 'statusId',
            'tasks', 'priorityOnly', 'sortField', 'sortDir'
        ));
    }

    public function create(Request $request)
    {
        $projects  = Project::orderBy('name')->get();
        $projectId = (int) $request->query('project_id', $projects->first()?->id);
        $aux       = $this->loadAux($projectId);

        return view('admin.tarefas.create', compact('projects', 'projectId', 'aux'));
    }

    public function store(StoreTarefaRequest $request)
    {
        $projectId = (int) $request->input('project_id');
        $data = $request->validated();
        $data['project_id']    = $projectId;
        $data['priority_flag'] = $request->boolean('priority_flag');
        $data['status']        = $request->boolean('status', true);

        Task::create($data);

        return redirect()
            ->route('admin.tarefas.index', ['project_id' => $projectId])
            ->with('success', 'Tarefa criada com sucesso.');
    }

    public function show($id)
    {
        $task = Task::with([
            'project', 'status', 'fase', 'modulo', 'tipo', 'prioridade',
        ])->findOrFail($id);

        $details = TaskDetail::with(['status', 'person'])
            ->where('task_id', $task->id)
            ->orderBy('started_at', 'desc')
            ->get();

        return view('admin.tarefas.show', compact('task', 'details'));
    }

    public function edit($id)
    {
        $task     = Task::findOrFail($id);
        $projects = Project::orderBy('name')->get();
        $aux      = $this->loadAux($task->project_id);

        return view('admin.tarefas.edit', compact('task', 'projects', 'aux'));
    }

    public function update(UpdateTarefaRequest $request, $id)
    {
        $task = Task::findOrFail($id);
        $data = $request->validated();
        unset($data['project_id']);
        $data['priority_flag'] = $request->boolean('priority_flag');
        $data['status']        = $request->boolean('status');

        $task->update($data);

        return redirect()
            ->route('admin.tarefas.show', $task->id)
            ->with('success', 'Tarefa atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $task         = Task::findOrFail($id);
        $projectId    = $task->project_id;
        $detailsCount = $task->details()->count();
        $task->delete();

        $msg = 'Tarefa excluída.';
        if ($detailsCount > 0) {
            $msg .= " {$detailsCount} ciclo(s) de execução preservado(s).";
        }

        return redirect()
            ->route('admin.tarefas.index', ['project_id' => $projectId])
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
