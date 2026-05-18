<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProjectToken
{
    /**
     * Garante que o token usado tem project_id (escopo de projeto).
     * Tokens sem project_id NÃO acessam rotas internas (/doc/...).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user()?->currentAccessToken();

        if (!$token || empty($token->project_id)) {
            return response()->json([
                'message' => 'Token sem escopo de projeto. Use um token vinculado a um projeto.',
            ], 403);
        }

        // Disponibiliza o project_id no request para uso em controllers
        $request->attributes->set('project_id', $token->project_id);

        return $next($request);
    }
}
