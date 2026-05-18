<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Share\StoreShareRequest;
use App\Http\Resources\ShareResource;
use App\Models\Share;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShareController extends ApiController
{
    public function store(StoreShareRequest $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        $share = Share::create([
            'project_id'           => $this->projectId($request),
            'payload'              => $request->input('payload'),
            'created_by_token_id'  => $token->id,
            'expires_at'           => $request->input('expires_at'),
        ]);

        return (new ShareResource($share))
            ->response()
            ->setStatusCode(201);
    }

    public function resolve(Request $request, string $hash)
    {
        $share = Share::query()
            ->with('project:id,name,slug')
            ->where('hash', $hash)
            ->firstOrFail();

        if ($share->expires_at && $share->expires_at->isPast()) {
            abort(410, 'Link expirado.');
        }

        return new ShareResource($share);
    }
}
