<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminProject
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user()?->currentAccessToken();

        if (!$token || empty($token->project_id)) {
            return response()->json([
                'message' => 'Token sem escopo de projeto.',
            ], 403);
        }

        $project = Project::find($token->project_id);

        if (!$project || !$project->is_admin) {
            return response()->json([
                'message' => 'Acesso restrito a projetos administradores.',
            ], 403);
        }

        return $next($request);
    }
}
