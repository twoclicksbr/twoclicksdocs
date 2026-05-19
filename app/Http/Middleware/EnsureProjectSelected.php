<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class EnsureProjectSelected
{
    public function handle(Request $request, Closure $next)
    {
        $id = Session::get('current_project_id');

        if (!$id) {
            return redirect()->route('admin.select-project');
        }

        $project = Project::find((int) $id);
        if (!$project || !$project->status) {
            Session::forget('current_project_id');
            return redirect()->route('admin.select-project');
        }

        return $next($request);
    }
}
