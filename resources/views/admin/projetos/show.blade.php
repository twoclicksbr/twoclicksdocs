@extends('admin.layouts.app')

@section('title', $project->name)

@section('header-actions')
    <a href="{{ route('admin.projetos.edit', $project->id) }}" class="btn btn-light btn-sm">Editar dados</a>
    <a href="{{ route('admin.projetos.index') }}" class="btn btn-light btn-sm">Voltar</a>
@endsection

@section('content')

@if(session('new_token'))
    <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6 mb-8">
        <i class="ki-outline ki-information-5 fs-2tx text-warning me-4"></i>
        <div class="d-flex flex-stack flex-grow-1">
            <div class="fw-semibold">
                <div class="fs-6 text-gray-700 mb-3">Token criado. <strong>Copie agora</strong> — não será exibido de novo:</div>
                <code class="d-block p-4 bg-dark text-warning rounded fs-7 break-all">{{ session('new_token') }}</code>
            </div>
        </div>
    </div>
@endif

<div class="row g-5 mb-8">
    <div class="col-md-4">
        <div class="card card-flush">
            <div class="card-header pt-5"><div class="card-title"><span class="text-muted fw-semibold fs-7 d-block mb-1">Slug</span></div></div>
            <div class="card-body pt-0"><code class="fs-5">{{ $project->slug }}</code></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-flush">
            <div class="card-header pt-5"><div class="card-title"><span class="text-muted fw-semibold fs-7 d-block mb-1">Status</span></div></div>
            <div class="card-body pt-0">
                @if($project->status)
                    <span class="badge badge-light-success">Ativo</span>
                @else
                    <span class="badge badge-light-danger">Inativo</span>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-flush">
            <div class="card-header pt-5"><div class="card-title"><span class="text-muted fw-semibold fs-7 d-block mb-1">Criado em</span></div></div>
            <div class="card-body pt-0"><span class="fw-semibold">{{ $project->created_at?->format('d/m/Y H:i') }}</span></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header border-0 pt-6 align-items-center">
        <div class="card-title">
            <h3 class="fw-bold mb-0">Tokens</h3>
        </div>
        <div class="card-toolbar">
            <form method="POST" action="{{ route('admin.projetos.tokens.store', $project->id) }}" class="d-flex gap-2">
                @csrf
                <input type="text" name="name" placeholder="Nome do token (ex: alex, claude)"
                       required class="form-control form-control-solid w-250px">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="ki-outline ki-plus fs-4"></i> Gerar token
                </button>
            </form>
        </div>
    </div>
    <div class="card-body py-4">
        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
            <thead>
                <tr class="fw-bold text-muted">
                    <th class="min-w-50px">ID</th>
                    <th>Nome</th>
                    <th>Criado em</th>
                    <th>Último uso</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tokens as $t)
                    <tr>
                        <td><span class="text-muted fw-semibold">{{ $t->id }}</span></td>
                        <td><code>{{ $t->name }}</code></td>
                        <td><span class="text-muted fs-7">{{ $t->created_at?->format('d/m/Y H:i') }}</span></td>
                        <td><span class="text-muted fs-7">{{ $t->last_used_at?->format('d/m/Y H:i') ?? '—' }}</span></td>
                        <td class="text-end">
                            <form action="{{ route('admin.projetos.tokens.destroy', [$project->id, $t->id]) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Revogar este token? Quem estiver usando perde acesso.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm"
                                        title="Revogar">
                                    <i class="ki-outline ki-trash fs-3"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-10">Nenhum token. Gere o primeiro acima.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
