<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AuthController as AdminAuth;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentoController;
use App\Http\Controllers\Admin\PessoaController;
use App\Http\Controllers\Admin\ProjetoController;
use App\Http\Controllers\Admin\TarefaController;
use App\Http\Controllers\Admin\TaskFaseController;
use App\Http\Controllers\Admin\TaskModuloController;
use App\Http\Controllers\Admin\TaskPrioridadeController;
use App\Http\Controllers\Admin\TaskStatusController;
use App\Http\Controllers\Admin\TaskTipoController;
use App\Http\Controllers\Admin\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// /painel descontinuado — redireciona pro admin
Route::get('/painel', fn() => redirect('/admin'));
Route::get('/painel/{any?}', fn() => redirect('/admin'))->where('any', '.*');

// Admin
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminAuth::class, 'showLogin'])->name('login');
    Route::post('login', [AdminAuth::class, 'login'])->name('login.attempt');

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AdminAuth::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Projetos (resource + tokens)
        Route::resource('projetos', ProjetoController::class);
        Route::post('projetos/{projeto}/tokens', [ProjetoController::class, 'createToken'])->name('projetos.tokens.store');
        Route::delete('projetos/{projeto}/tokens/{token}', [ProjetoController::class, 'revokeToken'])->name('projetos.tokens.destroy');

        // Pessoas e usuários
        Route::resource('pessoas', PessoaController::class);
        Route::resource('usuarios', UsuarioController::class);

        // Tabelas auxiliares
        Route::resource('task-statuses', TaskStatusController::class);
        Route::resource('task-fases', TaskFaseController::class);
        Route::resource('task-modulos', TaskModuloController::class);
        Route::resource('task-tipos', TaskTipoController::class);
        Route::resource('task-prioridades', TaskPrioridadeController::class);

        // Conteúdo (listagem global por projeto)
        Route::get('documentos', [DocumentoController::class, 'index'])->name('documentos.index');
        Route::get('documentos/{id}', [DocumentoController::class, 'show'])->name('documentos.show');
        Route::get('tarefas', [TarefaController::class, 'index'])->name('tarefas.index');
        Route::get('tarefas/{id}', [TarefaController::class, 'show'])->name('tarefas.show');

        // Auditoria
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});
