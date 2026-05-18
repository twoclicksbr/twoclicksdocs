<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends ApiController
{
    /**
     * GET /api/projects
     * Filtros: ?status=true&order=created_at,desc&per_page=100&search=smart&expand=
     */
    public function index(Request $request)
    {
        $query = Project::query()->expand($request);

        // Filtros exatos
        $this->applyFilters($query, $request, ['status']);

        // Busca textual em name e slug
        $this->applySearch($query, $request, ['name', 'slug']);

        // Ordenação (padrão: order asc)
        $this->applyOrder($query, $request, 'order,asc');

        $paginated = $query->paginate($this->perPage($request));

        return ProjectResource::collection($paginated);
    }

    /**
     * POST /api/projects
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = Project::create($request->validated());

        return (new ProjectResource($project))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/projects/{project}
     */
    public function show(Request $request, int $project): ProjectResource
    {
        $model = Project::query()
            ->expand($request)
            ->findOrFail($project);

        return new ProjectResource($model);
    }

    /**
     * PUT /api/projects/{project}
     */
    public function update(UpdateProjectRequest $request, int $project): ProjectResource
    {
        $model = Project::findOrFail($project);
        $model->update($request->validated());

        return new ProjectResource($model->fresh());
    }

    /**
     * DELETE /api/projects/{project}
     */
    public function destroy(int $project): JsonResponse
    {
        $model = Project::findOrFail($project);
        $model->delete();

        return response()->json([
            'message' => 'Projeto removido.',
        ]);
    }
}
