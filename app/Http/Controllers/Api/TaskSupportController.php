<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TaskFaseResource;
use App\Http\Resources\TaskModuloResource;
use App\Http\Resources\TaskPrioridadeResource;
use App\Http\Resources\TaskStatusResource;
use App\Http\Resources\TaskTipoResource;
use App\Models\TaskFase;
use App\Models\TaskModulo;
use App\Models\TaskPrioridade;
use App\Models\TaskStatus;
use App\Models\TaskTipo;
use Illuminate\Http\Request;

class TaskSupportController extends ApiController
{
    public function statuses(Request $request)
    {
        $query = TaskStatus::query()->orderBy('order');
        $this->applyFilters($query, $request, ['status']);
        return TaskStatusResource::collection($query->get());
    }

    public function fases(Request $request)
    {
        $query = TaskFase::query()->orderBy('order');
        $this->applyFilters($query, $request, ['status']);
        return TaskFaseResource::collection($query->get());
    }

    public function modulos(Request $request)
    {
        $query = TaskModulo::query()->orderBy('order');
        $this->applyFilters($query, $request, ['status']);
        return TaskModuloResource::collection($query->get());
    }

    public function tipos(Request $request)
    {
        $query = TaskTipo::query()->orderBy('order');
        $this->applyFilters($query, $request, ['status']);
        return TaskTipoResource::collection($query->get());
    }

    public function prioridades(Request $request)
    {
        $query = TaskPrioridade::query()->orderBy('order');
        $this->applyFilters($query, $request, ['status']);
        return TaskPrioridadeResource::collection($query->get());
    }
}
