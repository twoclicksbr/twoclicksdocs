<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use Illuminate\Support\Facades\Route;

// Rotas públicas
Route::post('/auth/login', [AuthController::class, 'login']);

// Rotas autenticadas (qualquer token Sanctum válido)
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Projects (meta-recurso, sem filtro por project_id do token)
    Route::apiResource('projects', ProjectController::class);

    // Rotas internas (filtradas pelo project_id do token)
    Route::middleware('project.token')->prefix('doc')->group(function () {

        // Documents
        Route::apiResource('documents', \App\Http\Controllers\Api\DocumentController::class);

        // Document Blocks (aninhado em documents)
        Route::apiResource('documents.blocks', \App\Http\Controllers\Api\DocumentBlockController::class)
            ->parameters(['blocks' => 'block']);

        // Tasks
        Route::apiResource('tasks', \App\Http\Controllers\Api\TaskController::class);

        // Task Details (aninhado em tasks)
        Route::apiResource('tasks.details', \App\Http\Controllers\Api\TaskDetailController::class)
            ->parameters(['details' => 'detail']);

        // Tabelas de apoio (read-only)
        Route::get('task-statuses',    [\App\Http\Controllers\Api\TaskSupportController::class, 'statuses']);
        Route::get('task-fases',       [\App\Http\Controllers\Api\TaskSupportController::class, 'fases']);
        Route::get('task-modulos',     [\App\Http\Controllers\Api\TaskSupportController::class, 'modulos']);
        Route::get('task-tipos',       [\App\Http\Controllers\Api\TaskSupportController::class, 'tipos']);
        Route::get('task-prioridades', [\App\Http\Controllers\Api\TaskSupportController::class, 'prioridades']);
    });
});
