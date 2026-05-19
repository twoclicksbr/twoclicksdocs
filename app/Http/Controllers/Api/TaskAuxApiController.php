<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

abstract class TaskAuxApiController extends ApiController
{
    abstract protected function modelClass(): string;
    abstract protected function resourceClass(): string;

    /** Rules for store. Keys that are 'required' become 'sometimes|required' on update. */
    abstract protected function rules(int $projectId, ?int $id = null): array;

    protected function searchColumns(): array
    {
        return ['name', 'slug'];
    }

    public function index(Request $request)
    {
        $projectId = $this->projectId($request);
        $model     = $this->modelClass();

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

        $model    = $this->modelClass();
        $item     = $model::create($data);
        $resource = $this->resourceClass();

        return (new $resource($item->fresh()))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $id)
    {
        $projectId = $this->projectId($request);
        $model     = $this->modelClass();
        $item      = $model::where('project_id', $projectId)->findOrFail($id);
        $resource  = $this->resourceClass();

        return new $resource($item);
    }

    public function update(Request $request, int $id)
    {
        $projectId = $this->projectId($request);
        $model     = $this->modelClass();
        $item      = $model::where('project_id', $projectId)->findOrFail($id);

        // Make all rules optional for PATCH/PUT partial updates
        $rules = collect($this->rules($projectId, $id))
            ->map(function ($rule) {
                if (is_array($rule)) {
                    return in_array('required', $rule)
                        ? array_merge(['sometimes'], $rule)
                        : $rule;
                }
                return str_contains($rule, 'required') ? 'sometimes|' . $rule : $rule;
            })
            ->all();

        $data = Validator::make($request->all(), $rules)->validate();
        $item->update($data);

        $resource = $this->resourceClass();
        return new $resource($item->fresh());
    }

    public function destroy(Request $request, int $id)
    {
        $projectId = $this->projectId($request);
        $model     = $this->modelClass();
        $item      = $model::where('project_id', $projectId)->findOrFail($id);
        $item->delete();

        return response()->json(null, 204);
    }
}
