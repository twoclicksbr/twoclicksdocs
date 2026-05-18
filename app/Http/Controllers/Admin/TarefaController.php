<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDetail;
use App\Models\TaskStatus;
use Illuminate\Http\Request;

class TarefaController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::orderBy('name')->get();
        $statuses = TaskStatus::orderBy('order')->get();
        $projectId = $request->query('project_id', $projects->first()?->id);
        $statusId  = $request->query('task_status_id');

        $tasks = collect();
        if ($projectId) {
            $q = Task::with(['status', 'fase', 'modulo', 'tipo', 'prioridade'])
                ->where('project_id', $projectId);
            if ($statusId) $q->where('task_status_id', $statusId);
            $tasks = $q->orderBy('order')->get();
        }

        return view('admin.tarefas.index', compact('projects', 'statuses', 'projectId', 'statusId', 'tasks'));
    }

    public function show($id)
    {
        $task = Task::with([
            'project',
            'status',
            'fase',
            'modulo',
            'tipo',
            'prioridade',
        ])->findOrFail($id);

        $details = TaskDetail::with(['status', 'person'])
            ->where('task_id', $task->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.tarefas.show', compact('task', 'details'));
    }
}
