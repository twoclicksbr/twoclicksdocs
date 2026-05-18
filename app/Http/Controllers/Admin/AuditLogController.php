<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Project;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with(['person', 'project'])
            ->orderBy('id', 'desc');

        if ($p = $request->query('project_id')) $query->where('project_id', $p);
        if ($a = $request->query('action'))     $query->where('action', $a);
        if ($t = $request->query('table_name')) $query->where('table_name', $t);

        $logs = $query->paginate(100)->withQueryString();
        $projects = Project::orderBy('name')->get();

        return view('admin.audit-logs.index', compact('logs', 'projects'));
    }
}
