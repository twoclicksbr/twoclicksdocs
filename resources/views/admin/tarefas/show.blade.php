@extends('admin.layouts.app')

@section('title', $task->title)
@section('header', $task->title)

@section('header-actions')
    <a href="{{ route('admin.tarefas.index', ['project_id' => $task->project_id]) }}"
       class="text-sm px-3 py-1.5 rounded border border-tc-border hover:bg-tc-card">
        Voltar
    </a>
@endsection

@section('content')

<div class="grid grid-cols-12 gap-4">

    {{-- Metadados --}}
    <aside class="col-span-4 space-y-3">
        <div class="bg-tc-card border border-tc-border rounded p-4">
            <div class="text-xs uppercase text-gray-400">Projeto</div>
            <div class="font-medium">{{ $task->project?->name ?? '—' }}</div>
        </div>
        <div class="bg-tc-card border border-tc-border rounded p-4">
            <div class="text-xs uppercase text-gray-400">Status</div>
            <div class="font-medium">{{ $task->getStatusRelation()?->name ?? '—' }}</div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-tc-card border border-tc-border rounded p-3">
                <div class="text-xs uppercase text-gray-400">Fase</div>
                <div class="text-sm">{{ $task->fase?->name ?? '—' }}</div>
            </div>
            <div class="bg-tc-card border border-tc-border rounded p-3">
                <div class="text-xs uppercase text-gray-400">Módulo</div>
                <div class="text-sm">{{ $task->modulo?->name ?? '—' }}</div>
            </div>
            <div class="bg-tc-card border border-tc-border rounded p-3">
                <div class="text-xs uppercase text-gray-400">Tipo</div>
                <div class="text-sm">{{ $task->tipo?->name ?? '—' }}</div>
            </div>
            <div class="bg-tc-card border border-tc-border rounded p-3">
                <div class="text-xs uppercase text-gray-400">Prioridade</div>
                <div class="text-sm">
                    @if($task->prioridade)
                        <span style="color:{{ $task->prioridade->color }}">{{ $task->prioridade->name }}</span>
                    @else
                        —
                    @endif
                </div>
            </div>
        </div>
        @if($task->description)
            <div class="bg-tc-card border border-tc-border rounded p-4">
                <div class="text-xs uppercase text-gray-400 mb-2">Descrição</div>
                <div class="text-sm whitespace-pre-wrap">{{ $task->description }}</div>
            </div>
        @endif
    </aside>

    {{-- Ciclos --}}
    <main class="col-span-8 bg-tc-card border border-tc-border rounded p-6">
        <h2 class="text-sm uppercase text-gray-400 mb-4">Ciclos de execução ({{ $details->count() }})</h2>

        @if($details->isEmpty())
            <div class="text-gray-500 text-sm">Sem ciclos registrados.</div>
        @else
            <div class="space-y-3">
                @foreach($details as $d)
                    <div class="bg-tc-dark border border-tc-border rounded p-3 text-sm">
                        <div class="flex justify-between text-xs text-gray-400 mb-2">
                            <span>
                                {{ $d->started_at?->format('d/m/Y H:i') ?? '—' }}
                                →
                                {{ $d->finished_at?->format('d/m/Y H:i') ?? 'em aberto' }}
                            </span>
                            <span>{{ $d->duration_minutes !== null ? $d->duration_minutes . ' min' : '—' }}</span>
                        </div>
                        <div class="text-xs text-gray-400 mb-2">
                            Status: <span class="text-gray-200">{{ $d->status?->name ?? '—' }}</span>
                            · Por: <span class="text-gray-200">{{ $d->person?->first_name ?? '—' }}</span>
                        </div>
                        <div class="whitespace-pre-wrap mb-2">{{ $d->prompt }}</div>
                        @if($d->resumo)
                            <div class="mt-2 pt-2 border-t border-tc-border text-gray-400 whitespace-pre-wrap text-xs">
                                <span class="uppercase">Resumo:</span> {{ $d->resumo }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </main>
</div>

@endsection
