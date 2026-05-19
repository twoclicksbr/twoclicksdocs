<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class SelectProjectController extends Controller
{
    public function index()
    {
        $projects = Project::where('status', true)->orderBy('name')->get();
        return view('admin.select-project', compact('projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|integer|exists:tc_doc.projects,id',
        ]);

        $request->session()->put('current_project_id', (int) $request->input('project_id'));

        return redirect()->route('admin.dashboard');
    }
}
