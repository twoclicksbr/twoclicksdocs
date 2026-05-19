@extends('admin.layouts.app')

@section('title', 'Tarefas')

@section('header-actions')
    <a href="{{ route('admin.tarefas.create', $projectId ? ['project_id' => $projectId] : []) }}"
       class="btn btn-primary btn-sm">
        <i class="ki-outline ki-plus fs-4"></i> Nova Tarefa
    </a>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success d-flex align-items-center mb-5">
        <i class="ki-outline ki-check-circle fs-2 text-success me-3"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif

<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <form method="GET" id="filterForm" class="d-flex align-items-center gap-3 flex-wrap">
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
                <div class="form-check form-switch ms-1">
                    <input class="form-check-input" type="checkbox" id="priorityCheck" name="priority_flag" value="true"
                           {{ $priorityOnly ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold text-gray-600 fs-7" for="priorityCheck">
                        Somente prioridades
                    </label>
                </div>
                @if($sortField !== 'order' || $sortDir !== 'asc')
                    <input type="hidden" name="sort" value="{{ $sortField }}">
                    <input type="hidden" name="dir" value="{{ $sortDir }}">
                @endif
                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
            </form>
        </div>
    </div>
    <div class="card-body py-4">
        @php
            $sh = function(string $label, string $field) use ($sortField, $sortDir): string {
                $nextDir = ($sortField === $field && $sortDir === 'asc') ? 'desc' : 'asc';
                $url     = request()->fullUrlWithQuery(['sort' => $field, 'dir' => $nextDir]);
                $arrow   = $sortField === $field
                    ? '<i class="ki-outline ki-arrow-' . ($sortDir === 'asc' ? 'up' : 'down') . ' fs-7"></i>'
                    : '';
                return '<a href="' . e($url) . '" class="text-muted text-hover-primary d-inline-flex align-items-center gap-1 text-nowrap">'
                     . e($label) . ' ' . $arrow . '</a>';
            };
        @endphp
        <div class="table-responsive">
        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
            <thead>
                <tr class="fw-bold text-muted">
                    <th class="min-w-50px">{!! $sh('ID', 'id') !!}</th>
                    <th>{!! $sh('Título', 'title') !!}</th>
                    <th>{!! $sh('Status', 'task_status_id') !!}</th>
                    <th>{!! $sh('Fase', 'task_fase_id') !!}</th>
                    <th>{!! $sh('Módulo', 'task_modulo_id') !!}</th>
                    <th>Tipo</th>
                    <th>{!! $sh('Prioridade', 'task_prioridade_id') !!}</th>
                    <th class="text-center min-w-80px">{!! $sh('Flag', 'priority_flag') !!}</th>
                    <th class="text-center min-w-60px">{!! $sh('Ordem', 'order') !!}</th>
                    <th class="text-end min-w-110px">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $t)
                    <tr class="cursor-pointer" onclick="window.location='{{ route('admin.tarefas.show', $t->id) }}'">
                        <td><span class="text-muted fw-semibold">{{ $t->id }}</span></td>
                        <td><span class="fw-bold text-gray-900 text-hover-primary">{{ \Illuminate\Support\Str::limit($t->title, 60) }}</span></td>
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
                        <td class="text-center">
                            @if($t->priority_flag)
                                <span class="badge badge-danger d-inline-flex align-items-center gap-1">
                                    <i class="ki-solid ki-flag fs-8 text-white"></i> Prio
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="text-muted fs-7">{{ $t->order }}</span>
                        </td>
                        <td class="text-end" onclick="event.stopPropagation()">
                            <a href="{{ route('admin.tarefas.show', $t->id) }}"
                               class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
                               title="Visualizar">
                                <i class="ki-outline ki-eye fs-3"></i>
                            </a>
                            <a href="{{ route('admin.tarefas.edit', $t->id) }}"
                               class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
                               title="Editar">
                                <i class="ki-outline ki-pencil fs-3"></i>
                            </a>
                            <form action="{{ route('admin.tarefas.destroy', $t->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Confirma a exclusão desta tarefa?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm"
                                        title="Excluir">
                                    <i class="ki-outline ki-trash fs-3"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-10">Sem tarefas.</td>
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
    const KEY_PROJECT  = 'tcdoc_admin_tarefas_project_id';
    const KEY_STATUS   = 'tcdoc_admin_tarefas_task_status_id';
    const KEY_PRIORITY = 'tcdoc_admin_tarefas_priority_flag';

    const form          = document.getElementById('filterForm');
    const projectSelect = document.getElementById('projectSelect');
    const statusSelect  = document.getElementById('statusSelect');
    const priorityCheck = document.getElementById('priorityCheck');

    const params = new URLSearchParams(window.location.search);
    const hasUrlProject = params.has('project_id');

    if (!hasUrlProject) {
        const savedProject  = localStorage.getItem(KEY_PROJECT);
        const savedStatus   = localStorage.getItem(KEY_STATUS);
        const savedPriority = localStorage.getItem(KEY_PRIORITY);
        if (savedProject) {
            const url = new URL(window.location.href);
            url.searchParams.set('project_id', savedProject);
            if (savedStatus)              url.searchParams.set('task_status_id', savedStatus);
            if (savedPriority === 'true') url.searchParams.set('priority_flag', 'true');
            window.location.replace(url.toString());
            return;
        }
    } else {
        const urlProject  = params.get('project_id');
        const urlStatus   = params.get('task_status_id') || '';
        const urlPriority = params.get('priority_flag') || '';
        if (urlProject) localStorage.setItem(KEY_PROJECT, urlProject);
        localStorage.setItem(KEY_STATUS,   urlStatus);
        localStorage.setItem(KEY_PRIORITY, urlPriority);
    }

    projectSelect.addEventListener('change', function() {
        statusSelect.value    = '';
        priorityCheck.checked = false;
        localStorage.setItem(KEY_PROJECT,  projectSelect.value);
        localStorage.setItem(KEY_STATUS,   '');
        localStorage.setItem(KEY_PRIORITY, '');
        form.submit();
    });

    statusSelect.addEventListener('change', function() {
        localStorage.setItem(KEY_STATUS, statusSelect.value);
        form.submit();
    });

    priorityCheck.addEventListener('change', function() {
        localStorage.setItem(KEY_PRIORITY, priorityCheck.checked ? 'true' : '');
        form.submit();
    });
})();
</script>
@endpush

@endsection
