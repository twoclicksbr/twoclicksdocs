<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserApiController extends ApiController
{
    public function index(Request $request)
    {
        $query = User::with('person');

        $this->applySearch($query, $request, ['email']);
        $this->applyOrder($query, $request, 'id,asc');

        return UserResource::collection(
            $query->paginate($this->perPage($request))
        );
    }

    public function show(int $id): UserResource
    {
        return new UserResource(User::with('person')->findOrFail($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'person_id' => 'required|integer|exists:tc_doc.people,id',
            'email'     => 'required|email|max:255|unique:tc_doc.users,email',
            'password'  => 'required|string|min:8',
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return (new UserResource($user->load('person')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, int $id): UserResource
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'person_id' => 'sometimes|required|integer|exists:tc_doc.people,id',
            'email'     => ['sometimes', 'required', 'email', 'max:255',
                Rule::unique('tc_doc.users', 'email')->ignore($user->id)],
            'password'  => 'sometimes|nullable|string|min:8',
        ]);

        if (isset($data['password']) && $data['password'] !== null) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return new UserResource($user->fresh()->load('person'));
    }

    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(null, 204);
    }
}
