@extends('admin.layouts.app')

@section('title', 'Auditoria')

@section('content')
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <form method="GET" class="d-flex align-items-center gap-3 flex-wrap">
                <select name="project_id" class="form-select form-select-solid w-180px">
                    <option value="">Todos projetos</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
                <select name="action" class="form-select form-select-solid w-150px">
                    <option value="">Todas ações</option>
                    @foreach(['create', 'update', 'delete', 'restore', 'force_delete'] as $a)
                        <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
                <input type="text" name="table_name" value="{{ request('table_name') }}" placeholder="Tabela"
                       class="form-control form-control-solid w-150px">
                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                <button type="button" id="auditClearFilters" class="btn btn-light btn-sm" title="Limpar filtros salvos">
                    <i class="ki-outline ki-eraser fs-5"></i>
                </button>
            </form>
        </div>
    </div>
    <div class="card-body py-4">
        <div class="table-responsive">
        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3 fs-7">
            <thead>
                <tr class="fw-bold text-muted">
                    <th>Quando</th>
                    <th>Pessoa</th>
                    <th>Token</th>
                    <th>Projeto</th>
                    <th>Ação</th>
                    <th>Tabela</th>
                    <th>Registro</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $l)
                    <tr>
                        <td class="text-muted text-nowrap">{{ $l->created_at?->format('d/m H:i:s') }}</td>
                        <td class="fw-semibold">{{ $l->person?->first_name ?? '—' }}</td>
                        <td><code class="fs-8 text-muted">{{ $l->token_name ?? '—' }}</code></td>
                        <td>{{ $l->project?->name ?? '—' }}</td>
                        <td>
                            @php $actionColor = match($l->action) { 'create' => 'success', 'update' => 'primary', 'delete' => 'danger', 'force_delete' => 'danger', default => 'secondary' }; @endphp
                            <span class="badge badge-light-{{ $actionColor }}">{{ $l->action }}</span>
                        </td>
                        <td><code class="fs-8 text-muted">{{ $l->table_name }}</code></td>
                        <td class="text-muted">#{{ $l->record_id }}</td>
                        <td class="text-muted">{{ $l->ip_address ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-10">Sem registros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="mt-4">{{ $logs->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const KEY         = 'audit_logs_estado_global';
    const FILTER_KEYS = ['project_id', 'action', 'table_name'];
    const params      = new URLSearchParams(window.location.search);

    if (params.get('clear') === '1') {
        try { localStorage.removeItem(KEY); } catch (e) {}
        params.delete('clear');
        const s = params.toString();
        history.replaceState(null, '', window.location.pathname + (s ? '?' + s : ''));
        return;
    }

    if (FILTER_KEYS.some(k => params.has(k))) {
        const snapshot = {};
        FILTER_KEYS.forEach(k => { if (params.has(k)) snapshot[k] = params.get(k); });
        try { localStorage.setItem(KEY, JSON.stringify(snapshot)); } catch (e) {}
    } else {
        let stored = null;
        try { stored = JSON.parse(localStorage.getItem(KEY) || 'null'); } catch (e) {}
        if (stored && FILTER_KEYS.some(k => stored[k])) {
            const restore = new URLSearchParams();
            FILTER_KEYS.forEach(k => { if (stored[k]) restore.set(k, stored[k]); });
            window.location.replace(window.location.pathname + '?' + restore.toString());
            return;
        }
    }

    const clearBtn = document.getElementById('auditClearFilters');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            try { localStorage.removeItem(KEY); } catch (e) {}
            window.location.href = window.location.pathname + '?clear=1';
        });
    }
})();
</script>
@endpush
