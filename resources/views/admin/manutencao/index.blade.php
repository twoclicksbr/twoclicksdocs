@extends('admin.layouts.app')

@section('title', 'Manutenção')

@section('content')

@if(session('success'))
    <div class="alert alert-success d-flex align-items-center mb-5">
        <i class="ki-outline ki-check-circle fs-2 text-success me-3"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger d-flex align-items-center mb-5">
        <i class="ki-outline ki-information fs-2 text-danger me-3"></i>
        <div>{{ session('error') }}</div>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger d-flex align-items-start mb-5">
        <i class="ki-outline ki-information fs-2 text-danger me-3 mt-1"></i>
        <ul class="mb-0">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="card mb-5">
    <div class="card-header border-0 pt-5">
        <h3 class="card-title fw-bold">Atualizar sandbox com dump de produção</h3>
    </div>
    <div class="card-body pb-5">
        @if($isProd)
            <div class="alert alert-warning d-flex align-items-center">
                <i class="ki-outline ki-shield-cross fs-2 text-warning me-3"></i>
                <div>Ambiente de <strong>produção</strong> — esta operação está desabilitada por segurança.</div>
            </div>
        @else
            <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                <span class="text-muted fs-7">Último dump:</span>
                @if($last)
                    @php
                        $statusBadge = match($last->status) {
                            'success' => 'badge-light-success',
                            'failed'  => 'badge-light-danger',
                            'running' => 'badge-light-warning',
                            default   => 'badge-light',
                        };
                    @endphp
                    <span class="badge {{ $statusBadge }} fs-7">{{ $last->status }}</span>
                    <span class="fw-semibold fs-7">{{ $last->created_at?->format('d/m/Y H:i') }}</span>
                    @if($last->durationSeconds() !== null)
                        <span class="text-muted fs-8">({{ $last->durationSeconds() }}s)</span>
                    @endif
                @else
                    <span class="text-muted fs-7">Nunca executado</span>
                @endif
            </div>
            @if($last && $last->summary)
                <div class="text-muted fs-7 mb-4"><code>{{ $last->summary }}</code></div>
            @else
                <div class="mb-4"></div>
            @endif

            @if($running)
                <div class="alert alert-info d-flex align-items-center">
                    <span class="spinner-border spinner-border-sm me-3"></span>
                    <div>Dump <strong>#{{ $running->id }}</strong> em execução desde {{ $running->started_at?->format('H:i:s') ?? $running->created_at?->format('H:i:s') }}…</div>
                </div>
                <script>setTimeout(() => location.reload(), 3000);</script>
            @else
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#dumpModal">
                    <i class="ki-outline ki-arrows-circle fs-4"></i>
                    Atualizar sandbox com dump de produção
                </button>
            @endif
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header border-0 pt-5">
        <h3 class="card-title fw-bold">Histórico (últimos 20)</h3>
    </div>
    <div class="card-body py-4">
        <div class="table-responsive">
        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
            <thead>
                <tr class="fw-bold text-muted">
                    <th class="min-w-50px">#</th>
                    <th>Executado por</th>
                    <th>Iniciado</th>
                    <th>Finalizado</th>
                    <th>Duração</th>
                    <th>Status</th>
                    <th>Summary</th>
                    <th>Erro</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $d)
                    <tr>
                        <td><span class="text-muted fw-semibold">{{ $d->id }}</span></td>
                        <td>
                            @if($d->executedBy)
                                {{ $d->executedBy->first_name }} {{ $d->executedBy->surname }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><span class="fs-7">{{ $d->started_at?->format('d/m/Y H:i:s') ?? '—' }}</span></td>
                        <td><span class="fs-7">{{ $d->finished_at?->format('d/m/Y H:i:s') ?? '—' }}</span></td>
                        <td><span class="fs-7">{{ $d->durationSeconds() !== null ? $d->durationSeconds().'s' : '—' }}</span></td>
                        <td>
                            @php
                                $b = match($d->status) {
                                    'success' => 'badge-light-success',
                                    'failed'  => 'badge-light-danger',
                                    'running' => 'badge-light-warning',
                                    default   => 'badge-light',
                                };
                            @endphp
                            <span class="badge {{ $b }} fs-8">{{ $d->status }}</span>
                        </td>
                        <td>
                            @if($d->summary)
                                <code class="fs-8">{{ \Illuminate\Support\Str::limit($d->summary, 100) }}</code>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($d->error_message)
                                <code class="text-danger fs-8">{{ \Illuminate\Support\Str::limit($d->error_message, 80) }}</code>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-10">Sem histórico ainda.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

@unless($isProd)
<div class="modal fade" id="dumpModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.manutencao.dump-sandbox') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Atualizar sandbox</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong>ATENÇÃO</strong>: vai apagar todos os dados atuais do sandbox e substituir pelos dados de produção.<br>
                    Tabelas preservadas (mantêm dados do sandbox): <code>personal_access_tokens</code>, <code>failed_jobs</code>, <code>cache</code>, <code>cache_locks</code>, <code>sessions</code>, <code>jobs</code>, <code>sandbox_dumps</code>.
                </div>
                <label class="form-label fw-semibold">Digite <code>ATUALIZAR</code> para confirmar:</label>
                <input type="text" name="confirmation" autocomplete="off"
                       class="form-control form-control-solid" placeholder="ATUALIZAR" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">Confirmar e disparar</button>
            </div>
        </form>
    </div>
</div>
@endunless

@endsection
