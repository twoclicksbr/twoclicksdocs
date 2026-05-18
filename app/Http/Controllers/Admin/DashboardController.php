<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentBlock;
use App\Models\Person;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDetail;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'projects'     => Project::count(),
            'people'       => Person::count(),
            'users'        => User::count(),
            'documents'    => Document::count(),
            'blocks'       => DocumentBlock::count(),
            'tasks'        => Task::count(),
            'task_details' => TaskDetail::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
