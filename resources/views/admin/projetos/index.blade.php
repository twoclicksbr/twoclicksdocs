@extends('admin.layouts.app')

@section('title', 'Projetos')

@section('header-actions')
    <a href="{{ route('admin.projetos.create') }}" class="btn btn-primary btn-sm">
        <i class="ki-outline ki-plus fs-4"></i> Novo
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <form method="GET" class="d-flex align-items-center position-relative">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar..."
                       class="form-control form-control-solid w-250px ps-13">
            </form>
        </div>
    </div>
    <div class="card-body py-4">
        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
            <thead>
                <tr class="fw-bold text-muted">
                    <th class="min-w-50px">ID</th>
                    <th>Nome</th>
                    <th>Slug</th>
                    <th>Ordem</th>
                    <th>Ativo</th>
                    <th class="text-end min-w-120px">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td><span class="text-muted fw-semibold">{{ $item->id }}</span></td>
                        <td><span class="fw-bold">{{ $item->name }}</span></td>
                        <td><code class="fs-7">{{ $item->slug }}</code></td>
                        <td>{{ $item->order }}</td>
                        <td>
                            @if($item->status)
                                <span class="badge badge-light-success">Ativo</span>
                            @else
                                <span class="badge badge-light-danger">Inativo</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.projetos.show', $item->id) }}"
                               class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
                               title="Tokens">
                                <i class="ki-outline ki-key-square fs-3"></i>
                            </a>
                            <a href="{{ route('admin.projetos.edit', $item->id) }}"
                               class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
                               title="Editar">
                                <i class="ki-outline ki-pencil fs-3"></i>
                            </a>
                            <form action="{{ route('admin.projetos.destroy', $item->id) }}" method="POST" class="d-inline"
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
                        <td colspan="6" class="text-center text-muted py-10">Nenhum projeto.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $items->links() }}</div>
    </div>
</div>
@endsection
