<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\DocumentBlock\StoreDocumentBlockRequest;
use App\Http\Requests\DocumentBlock\UpdateDocumentBlockRequest;
use App\Http\Resources\DocumentBlockResource;
use App\Models\Document;
use App\Models\DocumentBlock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentBlockController extends ApiController
{
    /**
     * Garante que o document pertence ao projeto do token.
     * Retorna o Document ou aborta 404.
     */
    private function findDocumentOrFail(Request $request, int $documentId): Document
    {
        return Document::query()
            ->where('project_id', $this->projectId($request))
            ->findOrFail($documentId);
    }

    public function index(Request $request, int $document)
    {
        $this->findDocumentOrFail($request, $document);

        $query = DocumentBlock::query()
            ->where('document_id', $document)
            ->expand($request);

        $this->applyFilters($query, $request, ['status', 'parent_id']);
        $this->applySearch($query, $request, ['content']);
        $this->applyOrder($query, $request, 'order,asc');

        return DocumentBlockResource::collection(
            $query->paginate($this->perPage($request))
        );
    }

    public function store(StoreDocumentBlockRequest $request, int $document): JsonResponse
    {
        $this->findDocumentOrFail($request, $document);

        $block = DocumentBlock::create(array_merge(
            $request->validated(),
            ['document_id' => $document]
        ));

        return (new DocumentBlockResource($block))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $document, int $block): DocumentBlockResource
    {
        $this->findDocumentOrFail($request, $document);

        $model = DocumentBlock::query()
            ->where('document_id', $document)
            ->expand($request)
            ->findOrFail($block);

        return new DocumentBlockResource($model);
    }

    public function update(UpdateDocumentBlockRequest $request, int $document, int $block): DocumentBlockResource
    {
        $this->findDocumentOrFail($request, $document);

        $model = DocumentBlock::query()
            ->where('document_id', $document)
            ->findOrFail($block);

        $model->update($request->validated());

        return new DocumentBlockResource($model->fresh());
    }

    public function destroy(Request $request, int $document, int $block): JsonResponse
    {
        $this->findDocumentOrFail($request, $document);

        $model = DocumentBlock::query()
            ->where('document_id', $document)
            ->findOrFail($block);

        $model->delete();

        return response()->json([
            'message' => 'Bloco removido.',
        ]);
    }
}
