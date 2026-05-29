<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentBlock;
use App\Services\ProjectContext;
use Illuminate\Http\Request;

class DocumentoController extends Controller
{
    public function index()
    {
        $projectId = ProjectContext::currentId();

        $docs = Document::where('project_id', $projectId)
            ->orderBy('order')
            ->get();

        return view('admin.documentos.index', compact('docs'));
    }

    public function show($id)
    {
        $document = Document::with('project')->findOrFail($id);

        if ($document->project_id !== ProjectContext::currentId()) {
            abort(404);
        }

        $blocks = DocumentBlock::where('document_id', $document->id)
            ->orderBy('order')
            ->get();

        $tree = $this->buildBlockTree($blocks);

        $siblings = Document::where('project_id', $document->project_id)
            ->orderBy('order')
            ->get(['id', 'title', 'parent_id']);

        $childDocuments = Document::where('parent_id', $document->id)
            ->orderBy('order')
            ->get(['id', 'title']);

        return view('admin.documentos.show', compact('document', 'tree', 'siblings', 'childDocuments'));
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
