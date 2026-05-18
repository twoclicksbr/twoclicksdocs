<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Document\StoreDocumentRequest;
use App\Http\Requests\Document\UpdateDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends ApiController
{
    public function index(Request $request)
    {
        $projectId = $this->projectId($request);

        $query = Document::query()
            ->where('project_id', $projectId)
            ->expand($request);

        $this->applyFilters($query, $request, ['status', 'parent_id']);
        $this->applySearch($query, $request, ['title', 'slug']);
        $this->applyOrder($query, $request, 'order,asc');

        return DocumentResource::collection(
            $query->paginate($this->perPage($request))
        );
    }

    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $document = Document::create(array_merge(
            $request->validated(),
            ['project_id' => $this->projectId($request)]
        ));

        return (new DocumentResource($document))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $document): DocumentResource
    {
        $model = Document::query()
            ->where('project_id', $this->projectId($request))
            ->expand($request)
            ->findOrFail($document);

        return new DocumentResource($model);
    }

    public function update(UpdateDocumentRequest $request, int $document): DocumentResource
    {
        $model = Document::query()
            ->where('project_id', $this->projectId($request))
            ->findOrFail($document);

        $model->update($request->validated());

        return new DocumentResource($model->fresh());
    }

    public function destroy(Request $request, int $document): JsonResponse
    {
        $model = Document::query()
            ->where('project_id', $this->projectId($request))
            ->findOrFail($document);

        $model->delete();

        return response()->json([
            'message' => 'Documento removido.',
        ]);
    }
}
