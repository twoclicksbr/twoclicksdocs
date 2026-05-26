@extends('admin.layouts.app')

@section('title', $titlePlural)

@section('header-actions')
    <a href="{{ route("{$route}.create") }}"
       class="btn btn-primary btn-sm">
        <i class="ki-outline ki-plus fs-4"></i> Novo
    </a>
@endsection

@section('content')

<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <form method="GET" id="crudFilterForm" class="d-flex align-items-center gap-3 position-relative">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5" style="top:50%;transform:translateY(-50%)"></i>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar..."
                           class="form-control form-control-solid w-250px ps-13">
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Buscar</button>
                <button type="button" id="crudClearFilters" class="btn btn-light btn-sm" title="Limpar busca salva">
                    <i class="ki-outline ki-eraser fs-5"></i>
                </button>
            </form>
        </div>
    </div>
    <div class="card-body py-4">
        <div class="table-responsive">
        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
            <thead>
                <tr class="fw-bold text-muted">
                    <th class="min-w-50px">ID</th>
                    @foreach($fields as $f)
                        @if($f['in_table'] ?? true)
                            <th>{{ $f['label'] }}</th>
                        @endif
                    @endforeach
                    <th class="text-end min-w-100px">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td><span class="text-muted fw-semibold">{{ $item->id }}</span></td>
                        @foreach($fields as $f)
                            @if($f['in_table'] ?? true)
                                <td>
                                    @if(($f['type'] ?? 'text') === 'boolean')
                                        @if($item->{$f['name']})
                                            <span class="badge badge-light-success">Sim</span>
                                        @else
                                            <span class="badge badge-light-danger">Não</span>
                                        @endif
                                    @elseif(($f['type'] ?? 'text') === 'execute_badge')
                                        @if($item->webhook_url && $item->auto_execute_default)
                                            <span title="Execução automática ativa" class="fs-5">✅</span>
                                        @elseif($item->webhook_url)
                                            <span title="Webhook ativo, sem auto-execução" class="fs-5">⚙️</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    @else
                                        <span class="fw-semibold">{{ \Illuminate\Support\Str::limit((string) ($item->{$f['name']} ?? ''), 60) }}</span>
                                    @endif
                                </td>
                            @endif
                        @endforeach
                        <td class="text-end">
                            <a href="{{ route("{$route}.edit", $item->id) }}"
                               class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
                               title="Editar">
                                <i class="ki-outline ki-pencil fs-3"></i>
                            </a>
                            <form action="{{ route("{$route}.destroy", $item->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Confirma a exclusão?')">
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
                        <td colspan="99" class="text-center text-muted py-10">Nenhum registro.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        <div class="mt-4">
            {{ $items->links() }}
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function() {
    const route     = @json($route);
    const projectId = '{{ \App\Services\ProjectContext::currentId() ?? "global" }}';
    const KEY       = route.replace(/\./g, '_') + '_estado_' + projectId;
    const params    = new URLSearchParams(window.location.search);

    if (params.get('clear') === '1') {
        try { localStorage.removeItem(KEY); } catch (e) {}
        params.delete('clear');
        const s = params.toString();
        history.replaceState(null, '', window.location.pathname + (s ? '?' + s : ''));
        return;
    }

    if (params.has('search')) {
        try { localStorage.setItem(KEY, JSON.stringify({ search: params.get('search') || '' })); } catch (e) {}
    } else {
        let stored = null;
        try { stored = JSON.parse(localStorage.getItem(KEY) || 'null'); } catch (e) {}
        if (stored && stored.search) {
            window.location.replace(window.location.pathname + '?search=' + encodeURIComponent(stored.search));
            return;
        }
    }

    const clearBtn = document.getElementById('crudClearFilters');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            try { localStorage.removeItem(KEY); } catch (e) {}
            window.location.href = window.location.pathname + '?clear=1';
        });
    }
})();
</script>
@endpush
