<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use Illuminate\Support\Facades\Route;

// Webhook público do executor Code (autenticado por shared secret no header)
Route::post('/webhook/code', [\App\Http\Controllers\Api\WebhookCodeController::class, 'receive']);

// Rotas públicas
Route::post('/auth/login', [AuthController::class, 'login']);

// Rotas autenticadas (qualquer token Sanctum válido)
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Projects, People e Users — restritos a tokens de projetos administradores
    Route::middleware('admin.project')->group(function () {
        Route::apiResource('projects', ProjectController::class);
        Route::apiResource('people', \App\Http\Controllers\Api\PersonApiController::class);
        Route::apiResource('users',  \App\Http\Controllers\Api\UserApiController::class);
    });

    // Rotas internas (filtradas pelo project_id do token)
    Route::middleware('project.token')->prefix('doc')->group(function () {

        // Documents
        Route::apiResource('documents', \App\Http\Controllers\Api\DocumentController::class);

        // Document Blocks (aninhado em documents)
        Route::apiResource('documents.blocks', \App\Http\Controllers\Api\DocumentBlockController::class)
            ->parameters(['blocks' => 'block']);

        // Tasks — rotas extras devem vir ANTES do apiResource
        Route::post('tasks/bulk', [\App\Http\Controllers\Api\TaskController::class, 'bulkStore']);
        Route::patch('tasks/bulk-move-modulo', [\App\Http\Controllers\Api\TaskController::class, 'bulkMoveModulo']);
        Route::post('tasks/{task}/transition', [\App\Http\Controllers\Api\TaskController::class, 'transition']);
        Route::post('tasks/{task}/execute', [\App\Http\Controllers\Api\TaskController::class, 'execute']);
        Route::apiResource('tasks', \App\Http\Controllers\Api\TaskController::class);

        // Task Details (aninhado em tasks)
        Route::apiResource('tasks.details', \App\Http\Controllers\Api\TaskDetailController::class)
            ->parameters(['details' => 'detail']);

        // Tabelas de apoio (CRUD por projeto)
        Route::apiResource('task-statuses',    \App\Http\Controllers\Api\TaskStatusApiController::class);
        Route::apiResource('task-fases',       \App\Http\Controllers\Api\TaskFaseApiController::class);
        Route::apiResource('task-modulos',     \App\Http\Controllers\Api\TaskModuloApiController::class);
        Route::apiResource('task-tipos',       \App\Http\Controllers\Api\TaskTipoApiController::class);
        Route::apiResource('task-prioridades', \App\Http\Controllers\Api\TaskPrioridadeApiController::class);

        // Audit Logs (somente leitura)
        Route::get('audit-logs', [\App\Http\Controllers\Api\AuditLogController::class, 'index']);
        Route::get('audit-logs/{log}', [\App\Http\Controllers\Api\AuditLogController::class, 'show']);

        // Shares (criar requer token de projeto)
        Route::post('shares', [\App\Http\Controllers\Api\ShareController::class, 'store']);
    });

    // Resolver share por hash (qualquer token Sanctum válido, sem filtro de projeto)
    Route::get('/shares/{hash}', [\App\Http\Controllers\Api\ShareController::class, 'resolve']);
});
