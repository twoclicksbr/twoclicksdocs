@extends('admin.layouts.app')

@section('title', 'Tarefas')

@section('content')

<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <form method="GET" id="filterForm" class="d-flex align-items-center gap-3">
                <select name="project_id" id="projectSelect" class="form-select form-select-solid w-200px">
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ (string)$projectId === (string)$p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
                <select name="task_status_id" id="statusSelect" class="form-select form-select-solid w-180px">
                    <option value="">Todos status</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s->id }}" {{ (string)$statusId === (string)$s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
            </form>
        </div>
    </div>
    <div class="card-body py-4">
        <div class="table-responsive">
        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
            <thead>
                <tr class="fw-bold text-muted">
                    <th class="min-w-50px">ID</th>
                    <th>Título</th>
                    <th>Status</th>
                    <th>Fase</th>
                    <th>Módulo</th>
                    <th>Tipo</th>
                    <th>Prioridade</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $t)
                    <tr class="cursor-pointer" onclick="window.location='{{ route('admin.tarefas.show', $t->id) }}'">
                        <td><span class="text-muted fw-semibold">{{ $t->id }}</span></td>
                        <td><span class="fw-bold text-gray-900 text-hover-primary">{{ $t->title }}</span></td>
                        <td>
                            @if($t->getStatusRelation())
                                <span class="badge badge-light-primary">{{ $t->getStatusRelation()->name }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><span class="text-muted fs-7">{{ $t->fase?->name ?? '—' }}</span></td>
                        <td><span class="text-muted fs-7">{{ $t->modulo?->name ?? '—' }}</span></td>
                        <td><span class="text-muted fs-7">{{ $t->tipo?->name ?? '—' }}</span></td>
                        <td>
                            @if($t->prioridade)
                                <span class="fw-semibold fs-7" style="color:{{ $t->prioridade->color }}">{{ $t->prioridade->name }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-10">Sem tarefas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
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
