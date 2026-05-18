<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/auth/login
     * Body: { email, password, project_id, token_name }
     *
     * project_id e token_name são OPCIONAIS:
     * - Se ambos fornecidos: cria token vinculado ao projeto
     * - Se ausentes: cria token genérico (sem project_id) — uso só para emergência/admin
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'project_id' => 'nullable|integer|exists:tc_doc.projects,id',
            'token_name' => 'nullable|string|max:50',
        ]);

        if (!Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        $name = $validated['token_name'] ?? 'default';
        $token = $user->createToken($name);

        // Se project_id foi enviado, atualiza a coluna project_id do token
        if (!empty($validated['project_id'])) {
            $token->accessToken->update(['project_id' => $validated['project_id']]);
        }

        return response()->json([
            'data' => [
                'token' => $token->plainTextToken,
                'token_name' => $name,
                'project_id' => $validated['project_id'] ?? null,
                'user' => $user->load('person'),
            ],
        ]);
    }

    /**
     * POST /api/auth/logout
     * Revoga o token usado na request.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado.',
        ]);
    }

    /**
     * GET /api/auth/me
     * Retorna dados do user + project do token usado.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('person');
        $token = $request->user()->currentAccessToken();

        return response()->json([
            'data' => [
                'user' => $user,
                'token' => [
                    'name' => $token->name,
                    'project_id' => $token->project_id,
                    'last_used_at' => $token->last_used_at,
                ],
            ],
        ]);
    }
}
