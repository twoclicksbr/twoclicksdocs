@extends('admin.layouts.app')

@section('title', 'Tarefas')
@section('header', 'Tarefas')

@section('content')

<form method="GET" id="filterForm" class="mb-4 flex gap-2 text-sm">
    <select name="project_id" id="projectSelect" class="bg-tc-card border border-tc-border rounded px-3 py-1.5">
        @foreach($projects as $p)
            <option value="{{ $p->id }}" {{ (string)$projectId === (string)$p->id ? 'selected' : '' }}>{{ $p->name }}</option>
        @endforeach
    </select>
    <select name="task_status_id" id="statusSelect" class="bg-tc-card border border-tc-border rounded px-3 py-1.5">
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


@push('scripts')
<script>
(function() {
    const KEY_PROJECT = 'tcdoc_admin_tarefas_project_id';
    const KEY_STATUS = 'tcdoc_admin_tarefas_task_status_id';

    const form = document.getElementById('filterForm');
    const projectSelect = document.getElementById('projectSelect');
    const statusSelect = document.getElementById('statusSelect');

    const params = new URLSearchParams(window.location.search);
    const hasUrlProject = params.has('project_id');

    if (!hasUrlProject) {
        const savedProject = localStorage.getItem(KEY_PROJECT);
        const savedStatus = localStorage.getItem(KEY_STATUS);
        if (savedProject) {
            const url = new URL(window.location.href);
            url.searchParams.set('project_id', savedProject);
            if (savedStatus) url.searchParams.set('task_status_id', savedStatus);
            window.location.replace(url.toString());
            return;
        }
    } else {
        const urlProject = params.get('project_id');
        const urlStatus = params.get('task_status_id') || '';
        if (urlProject) localStorage.setItem(KEY_PROJECT, urlProject);
        localStorage.setItem(KEY_STATUS, urlStatus);
    }

    projectSelect.addEventListener('change', function() {
        statusSelect.value = '';
        localStorage.setItem(KEY_PROJECT, projectSelect.value);
        localStorage.setItem(KEY_STATUS, '');
        form.submit();
    });

    statusSelect.addEventListener('change', function() {
        localStorage.setItem(KEY_STATUS, statusSelect.value);
        form.submit();
    });
})();
</script>
@endpush

@endsection
