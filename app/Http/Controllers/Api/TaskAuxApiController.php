<?php

namespace App\Http\Controllers\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

abstract class TaskAuxApiController extends ApiController
{
    abstract protected function modelClass(): string;
    abstract protected function resourceClass(): string;
    abstract protected function rules(int $projectId, ?int $id = null): array;

    protected function searchColumns(): array
    {
        return ['name', 'slug'];
    }

    public function index(Request $request)
    {
        $projectId = $this->projectId($request);
        $model = $this->modelClass();

        $query = $model::query()
            ->where('project_id', $projectId)
            ->orderBy('order');

        $this->applyFilters($query, $request, ['status']);
        $this->applySearch($query, $request, $this->searchColumns());

        $resource = $this->resourceClass();
        return $resource::collection($query->get());
    }

    public function store(Request $request)
    {
        $projectId = $this->projectId($request);

        $data = Validator::make($request->all(), $this->rules($projectId))->validate();
        $data['project_id'] = $projectId;

        $model = $this->modelClass();
        $item = $model::create($data);

        $resource = $this->resourceClass();
        return new $resource($item);
    }

    public function show(Request $request, int $id)
    {
        $projectId = $this->projectId($request);
        $model = $this->modelClass();

        $item = $model::where('project_id', $projectId)->findOrFail($id);

        $resource = $this->resourceClass();
        return new $resource($item);
    }

    public function update(Request $request, int $id)
    {
        $projectId = $this->projectId($request);
        $model = $this->modelClass();

        $item = $model::where('project_id', $projectId)->findOrFail($id);

        $data = Validator::make($request->all(), $this->rules($projectId, $id))->validate();
        $item->update($data);

        $resource = $this->resourceClass();
        return new $resource($item->fresh());
    }

    public function destroy(Request $request, int $id)
    {
        $projectId = $this->projectId($request);
        $model = $this->modelClass();

        $item = $model::where('project_id', $projectId)->findOrFail($id);
        $item->delete();

        return response()->json(null, 204);
    }
}
