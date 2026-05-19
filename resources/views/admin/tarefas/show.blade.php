@extends('admin.layouts.app')

@section('title', $task->title)

@section('header-actions')
    <a href="{{ route('admin.tarefas.index', ['project_id' => $task->project_id]) }}"
       class="btn btn-light btn-sm me-2">Voltar</a>
    <a href="{{ route('admin.tarefas.edit', $task->id) }}"
       class="btn btn-primary btn-sm me-2">
        <i class="ki-outline ki-pencil fs-5"></i> Editar
    </a>
    <form action="{{ route('admin.tarefas.destroy', $task->id) }}" method="POST" class="d-inline"
          onsubmit="return confirm('Confirma a exclusão desta tarefa?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm">
            <i class="ki-outline ki-trash fs-5"></i> Excluir
        </button>
    </form>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success d-flex align-items-center mb-5">
        <i class="ki-outline ki-check-circle fs-2 text-success me-3"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif

<div class="row g-5">

    {{-- Metadados --}}
    <div class="col-lg-4">
        <div class="d-flex flex-column gap-4">

            {{-- Cabeçalho com badges --}}
            <div class="card card-flush">
                <div class="card-body py-4 px-5">
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        @if($task->priority_flag)
                            <span class="badge badge-danger d-inline-flex align-items-center gap-1">
                                <i class="ki-solid ki-flag fs-8 text-white"></i> Prioridade
                            </span>
                        @endif
                        @if($task->status)
                            <span class="badge badge-light-primary">{{ $task->status->name }}</span>
                        @endif
                        @if($task->prioridade)
                            <span class="badge badge-light" style="color:{{ $task->prioridade->color }}; border: 1px solid {{ $task->prioridade->color }}">
                                {{ $task->prioridade->name }}
                            </span>
                        @endif
                    </div>
                    <h2 class="fw-bold fs-3 mb-0">{{ $task->title }}</h2>
                </div>
            </div>

            <div class="card card-flush">
                <div class="card-body py-4 px-5">
                    <span class="text-muted fw-semibold fs-7 d-block mb-1 text-uppercase">Projeto</span>
                    <span class="fw-bold">{{ $task->project?->name ?? '—' }}</span>
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
                            <span class="text-muted fw-semibold fs-8 d-block mb-1 text-uppercase">Ordem</span>
                            <span class="fs-7 fw-semibold">{{ $task->order }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-flush">
                <div class="card-body py-3 px-5">
                    <div class="d-flex flex-column gap-1">
                        <span class="text-muted fs-8">Criado: {{ $task->created_at?->format('d/m/Y H:i') }}</span>
                        <span class="text-muted fs-8">Atualizado: {{ $task->updated_at?->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            @if($task->description)
                <div class="card card-flush">
                    <div class="card-body py-4 px-5">
                        <span class="text-muted fw-semibold fs-7 d-block mb-2 text-uppercase">Descrição</span>
                        <div class="fs-7 text-gray-700 markdown-body">
                            @markdown($task->description)
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- Ciclos de execução --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header border-0 pt-5">
                <div class="card-title">
                    <h3 class="fw-bold mb-0">
                        Ciclos de execução
                        <span class="badge badge-light-primary ms-2">{{ $details->count() }}</span>
                    </h3>
                </div>
            </div>
            <div class="card-body py-4">
                @if($details->isEmpty())
                    <div class="text-center text-muted py-8">
                        <i class="ki-outline ki-time fs-2x d-block mb-2 opacity-25"></i>
                        Nenhum ciclo de execução registrado.
                    </div>
                @else
                    <div class="d-flex flex-column gap-4">
                        @foreach($details as $d)
                            <div class="p-5 rounded bg-light border border-dashed border-gray-300">
                                <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
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
                                @if($d->prompt)
                                    <div class="fs-7 text-gray-800 fw-semibold" style="white-space: pre-wrap">{{ $d->prompt }}</div>
                                @endif
                                @if($d->resumo)
                                    <div class="mt-3 pt-3 border-top border-gray-300">
                                        <span class="text-muted fs-8 text-uppercase fw-bold d-block mb-1">Resumo:</span>
                                        <div class="fs-7 text-muted markdown-body">
                                            @markdown($d->resumo)
                                        </div>
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
