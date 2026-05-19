<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SwitchProjectController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|integer|exists:tc_doc.projects,id',
        ]);

        $request->session()->put('current_project_id', (int) $request->input('project_id'));

        return redirect()->route('admin.dashboard');
    }
}
