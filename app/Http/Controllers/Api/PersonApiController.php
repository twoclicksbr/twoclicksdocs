<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PersonResource;
use App\Models\Person;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PersonApiController extends ApiController
{
    public function index(Request $request)
    {
        $query = Person::query();

        $this->applySearch($query, $request, ['first_name', 'surname']);
        $this->applyOrder($query, $request, 'id,asc');

        return PersonResource::collection(
            $query->paginate($this->perPage($request))
        );
    }

    public function show(int $id): PersonResource
    {
        return new PersonResource(Person::with('user')->findOrFail($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'surname'    => 'required|string|max:255',
        ]);

        $person = Person::create($data);

        return (new PersonResource($person))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, int $id): PersonResource
    {
        $person = Person::findOrFail($id);

        $data = $request->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'surname'    => 'sometimes|required|string|max:255',
        ]);

        $person->update($data);

        return new PersonResource($person->fresh());
    }

    public function destroy(int $id): JsonResponse
    {
        $person = Person::findOrFail($id);
        $person->delete();

        return response()->json(null, 204);
    }
}
