<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Task;
use App\Services\ProjectContext;

class DashboardController extends Controller
{
    public function index()
    {
        $projectId = ProjectContext::currentId();

        $stats = [
            'documents' => Document::where('project_id', $projectId)->count(),
            'tasks'     => Task::where('project_id', $projectId)->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
