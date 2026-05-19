<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentBlock;
use App\Models\Task;
use App\Models\TaskDetail;
use App\Services\ProjectContext;

class DashboardController extends Controller
{
    public function index()
    {
        $projectId = ProjectContext::currentId();

        $stats = [
            'documents'    => Document::where('project_id', $projectId)->count(),
            'blocks'       => DocumentBlock::whereHas('document', fn($q) => $q->where('project_id', $projectId))->count(),
            'tasks'        => Task::where('project_id', $projectId)->count(),
            'task_details' => TaskDetail::whereHas('task', fn($q) => $q->where('project_id', $projectId))->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
