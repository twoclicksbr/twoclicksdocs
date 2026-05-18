@extends('admin.layouts.app')

@section('title', 'Tarefas')
@section('header', 'Tarefas')

@section('content')

<form method="GET" class="mb-4 flex gap-2 text-sm">
    <select name="project_id" class="bg-tc-card border border-tc-border rounded px-3 py-1.5">
        @foreach($projects as $p)
            <option value="{{ $p->id }}" {{ (string)$projectId === (string)$p->id ? 'selected' : '' }}>{{ $p->name }}</option>
        @endforeach
    </select>
    <select name="task_status_id" class="bg-tc-card border border-tc-border rounded px-3 py-1.5">
        <option value="">Todos status</option>
        @foreach($statuses as $s)
            <option value="{{ $s->id }}" {{ (string)$statusId === (string)$s->id ? 'selected' : '' }}>{{ $s->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded">Filtrar</button>
</form>

<div class="bg-tc-card border border-tc-border rounded overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-tc-dark border-b border-tc-border text-xs uppercase text-gray-400">
            <tr>
                <th class="px-3 py-2 text-left">ID</th>
                <th class="px-3 py-2 text-left">Título</th>
                <th class="px-3 py-2 text-left">Status</th>
                <th class="px-3 py-2 text-left">Fase</th>
                <th class="px-3 py-2 text-left">Módulo</th>
                <th class="px-3 py-2 text-left">Tipo</th>
                <th class="px-3 py-2 text-left">Prioridade</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $t)
                <tr class="border-b border-tc-border last:border-0 hover:bg-tc-dark cursor-pointer" onclick="window.location='{{ route('admin.tarefas.show', $t->id) }}'">
                    <td class="px-3 py-2 text-gray-400">{{ $t->id }}</td>
                    <td class="px-3 py-2 font-medium">{{ $t->title }}</td>
                    <td class="px-3 py-2">{{ $t->status?->name ?? '—' }}</td>
                    <td class="px-3 py-2 text-gray-400">{{ $t->fase?->name ?? '—' }}</td>
                    <td class="px-3 py-2 text-gray-400">{{ $t->modulo?->name ?? '—' }}</td>
                    <td class="px-3 py-2 text-gray-400">{{ $t->tipo?->name ?? '—' }}</td>
                    <td class="px-3 py-2 text-gray-400">{{ $t->prioridade?->name ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-3 py-6 text-center text-gray-500">Sem tarefas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>


@endsection
