<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentBlock;
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

    public function show($id)
    {
        $document = Document::with('project')->findOrFail($id);

        $blocks = DocumentBlock::where('document_id', $document->id)
            ->orderBy('order')
            ->get();

        $tree = $this->buildBlockTree($blocks);

        $siblings = Document::where('project_id', $document->project_id)
            ->orderBy('order')
            ->get(['id', 'title', 'parent_id']);

        return view('admin.documentos.show', compact('document', 'tree', 'siblings'));
    }

    private function buildBlockTree($blocks, $parentId = null): array
    {
        $tree = [];
        foreach ($blocks as $block) {
            if ($block->parent_id == $parentId) {
                $children = $this->buildBlockTree($blocks, $block->id);
                $tree[] = ['block' => $block, 'children' => $children];
            }
        }
        return $tree;
    }
}
