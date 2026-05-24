@extends('admin.layouts.app')

@section('title', 'Tarefas')

@section('header-actions')
    <a href="{{ route('admin.tarefas.create') }}"
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
                <button type="button" id="clearFilters" class="btn btn-light btn-sm" title="Limpar filtros salvos">
                    <i class="ki-outline ki-eraser fs-5"></i>
                </button>
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
    const projectId  = '{{ \App\Services\ProjectContext::currentId() ?? "0" }}';
    const KEY        = `tarefas_estado_${projectId}`;
    const OLD_KEY    = `tarefas_filtros_${projectId}`;
    const FILTER_KEYS = ['task_status_id', 'priority_flag'];
    const SORT_KEYS   = ['sort', 'dir'];
    const ALL_KEYS    = [...FILTER_KEYS, ...SORT_KEYS];

    // Migração: chave antiga → nova
    try {
        if (!localStorage.getItem(KEY) && localStorage.getItem(OLD_KEY)) {
            localStorage.setItem(KEY, localStorage.getItem(OLD_KEY));
            localStorage.removeItem(OLD_KEY);
        }
    } catch (e) {}

    const params = new URLSearchParams(window.location.search);

    if (params.get('clear') === '1') {
        try { localStorage.removeItem(KEY); } catch (e) {}
        params.delete('clear');
        const s = params.toString();
        history.replaceState(null, '', window.location.pathname + (s ? '?' + s : ''));
        return;
    }

    const urlHasState = ALL_KEYS.some(k => params.has(k));

    if (urlHasState) {
        const snapshot = {};
        ALL_KEYS.forEach(k => { if (params.has(k)) snapshot[k] = params.get(k); });
        try { localStorage.setItem(KEY, JSON.stringify(snapshot)); } catch (e) {}
    } else {
        let stored = null;
        try { stored = JSON.parse(localStorage.getItem(KEY) || 'null'); } catch (e) {}
        if (stored && ALL_KEYS.some(k => stored[k])) {
            const restore = new URLSearchParams();
            ALL_KEYS.forEach(k => { if (stored[k]) restore.set(k, stored[k]); });
            window.location.replace(window.location.pathname + '?' + restore.toString());
            return;
        }
    }

    const clearBtn = document.getElementById('clearFilters');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            try { localStorage.removeItem(KEY); } catch (e) {}
            window.location.href = window.location.pathname + '?clear=1';
        });
    }
})();
</script>
@endpush

@endsection
