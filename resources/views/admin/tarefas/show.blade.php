@extends('admin.layouts.app')

@section('title', $task->title)

@section('header-actions')
    <a href="{{ route('admin.tarefas.index', ['project_id' => $task->project_id]) }}"
       class="btn btn-light btn-sm">Voltar</a>
@endsection

@section('content')
<div class="row g-5">

    {{-- Metadados --}}
    <div class="col-lg-4">
        <div class="d-flex flex-column gap-4">
            <div class="card card-flush">
                <div class="card-body py-4 px-5">
                    <span class="text-muted fw-semibold fs-7 d-block mb-1 text-uppercase">Projeto</span>
                    <span class="fw-bold">{{ $task->project?->name ?? '—' }}</span>
                </div>
            </div>
            <div class="card card-flush">
                <div class="card-body py-4 px-5">
                    <span class="text-muted fw-semibold fs-7 d-block mb-1 text-uppercase">Status</span>
                    @if($task->getStatusRelation())
                        <span class="badge badge-light-primary">{{ $task->getStatusRelation()->name }}</span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>
            </div>
            <div class="row g-3">
                <div class="col-6">
                    <div class="card card-flush">
                        <div class="card-body py-3 px-4">
                            <span class="text-muted fw-semibold fs-8 d-block mb-1 text-uppercase">Fase</span>
                            <span class="fs-7 fw-semibold">{{ $task->fase?->name ?? '—' }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card card-flush">
                        <div class="card-body py-3 px-4">
                            <span class="text-muted fw-semibold fs-8 d-block mb-1 text-uppercase">Módulo</span>
                            <span class="fs-7 fw-semibold">{{ $task->modulo?->name ?? '—' }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card card-flush">
                        <div class="card-body py-3 px-4">
                            <span class="text-muted fw-semibold fs-8 d-block mb-1 text-uppercase">Tipo</span>
                            <span class="fs-7 fw-semibold">{{ $task->tipo?->name ?? '—' }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card card-flush">
                        <div class="card-body py-3 px-4">
                            <span class="text-muted fw-semibold fs-8 d-block mb-1 text-uppercase">Prioridade</span>
                            @if($task->prioridade)
                                <span class="fs-7 fw-bold" style="color:{{ $task->prioridade->color }}">{{ $task->prioridade->name }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @if($task->description)
                <div class="card card-flush">
                    <div class="card-body py-4 px-5">
                        <span class="text-muted fw-semibold fs-7 d-block mb-2 text-uppercase">Descrição</span>
                        <div class="fs-7 text-gray-700" style="white-space: pre-wrap">{{ $task->description }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Ciclos --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header border-0 pt-5">
                <div class="card-title">
                    <h3 class="fw-bold mb-0">Ciclos de execução <span class="badge badge-light-primary ms-2">{{ $details->count() }}</span></h3>
                </div>
            </div>
            <div class="card-body py-4">
                @if($details->isEmpty())
                    <div class="text-muted">Sem ciclos registrados.</div>
                @else
                    <div class="d-flex flex-column gap-4">
                        @foreach($details as $d)
                            <div class="p-5 rounded bg-light border border-dashed border-gray-300">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex flex-column gap-1">
                                        <span class="fs-7 text-muted">
                                            {{ $d->started_at?->format('d/m/Y H:i') ?? '—' }}
                                            →
                                            {{ $d->finished_at?->format('d/m/Y H:i') ?? 'em aberto' }}
                                        </span>
                                        <div class="fs-7 text-muted">
                                            Status: <span class="text-gray-700 fw-semibold">{{ $d->status?->name ?? '—' }}</span>
                                            · Por: <span class="text-gray-700 fw-semibold">{{ $d->person?->first_name ?? '—' }}</span>
                                        </div>
                                    </div>
                                    <span class="badge badge-light-secondary">
                                        {{ $d->duration_minutes !== null ? $d->duration_minutes . ' min' : '—' }}
                                    </span>
                                </div>
                                <div class="fs-7 text-gray-800 fw-semibold" style="white-space: pre-wrap">{{ $d->prompt }}</div>
                                @if($d->resumo)
                                    <div class="mt-3 pt-3 border-top border-gray-300">
                                        <span class="text-muted fs-8 text-uppercase fw-bold me-1">Resumo:</span>
                                        <span class="fs-7 text-muted" style="white-space: pre-wrap">{{ $d->resumo }}</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
