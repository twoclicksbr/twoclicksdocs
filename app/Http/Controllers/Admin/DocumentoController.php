<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Project;
use Illuminate\Http\Request;

class DocumentoController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::orderBy('name')->get();
        $projectId = $request->query('project_id', $projects->first()?->id);

        $docs = collect();
        if ($projectId) {
            $docs = Document::where('project_id', $projectId)
                ->orderBy('order')
                ->get();
        }

        return view('admin.documentos.index', compact('projects', 'projectId', 'docs'));
    }
}
