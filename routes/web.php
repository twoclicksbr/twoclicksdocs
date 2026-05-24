<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AuthController as AdminAuth;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ManutencaoController;
use App\Http\Controllers\Admin\DocumentoController;
use App\Http\Controllers\Admin\PessoaController;
use App\Http\Controllers\Admin\ProjetoController;
use App\Http\Controllers\Admin\SelectProjectController;
use App\Http\Controllers\Admin\SwitchProjectController;
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

        // Seleção de workspace (sem project.selected)
        Route::get('select-project', [SelectProjectController::class, 'index'])->name('select-project');
        Route::post('select-project', [SelectProjectController::class, 'store'])->name('select-project.store');
        Route::post('switch-project', [SwitchProjectController::class, 'store'])->name('switch-project');

        // Telas globais (sem project.selected)
        Route::resource('projetos', ProjetoController::class);
        Route::post('projetos/{projeto}/tokens', [ProjetoController::class, 'createToken'])->name('projetos.tokens.store');
        Route::delete('projetos/{projeto}/tokens/{token}', [ProjetoController::class, 'revokeToken'])->name('projetos.tokens.destroy');
        Route::resource('pessoas', PessoaController::class);
        Route::resource('usuarios', UsuarioController::class);
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        // Manutenção (dump sandbox a partir de prod) — só renderiza botão em não-prod
        Route::get('manutencao', [ManutencaoController::class, 'index'])->name('manutencao.index');
        Route::post('manutencao/dump-sandbox', [ManutencaoController::class, 'dumpSandbox'])->name('manutencao.dump-sandbox');

        // Telas project-scoped (exigem projeto na sessão)
        Route::middleware('project.selected')->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

            // Tabelas auxiliares
            Route::resource('task-statuses', TaskStatusController::class);
            Route::resource('task-fases', TaskFaseController::class);
            Route::resource('task-modulos', TaskModuloController::class);
            Route::resource('task-tipos', TaskTipoController::class);
            Route::resource('task-prioridades', TaskPrioridadeController::class);

            // Conteúdo
            Route::get('documentos', [DocumentoController::class, 'index'])->name('documentos.index');
            Route::get('documentos/{id}', [DocumentoController::class, 'show'])->name('documentos.show');
            Route::resource('tarefas', TarefaController::class);
            Route::get('api/projetos/{id}/auxiliares', [TarefaController::class, 'auxiliares'])->name('api.projetos.auxiliares');
        });
    });
});
