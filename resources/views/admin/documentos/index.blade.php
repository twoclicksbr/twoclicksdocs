@extends('admin.layouts.app')

@section('title', 'Documentos')

@section('content')
<div class="card">
    <div class="card-body py-4">
        <div class="table-responsive">
        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
            <thead>
                <tr class="fw-bold text-muted">
                    <th class="min-w-50px">ID</th>
                    <th>Título</th>
                    <th>Slug</th>
                    <th>Pai</th>
                    <th>Ordem</th>
                    <th>Ativo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($docs as $d)
                    <tr class="cursor-pointer" onclick="window.location='{{ route('admin.documentos.show', $d->id) }}'">
                        <td><span class="text-muted fw-semibold">{{ $d->id }}</span></td>
                        <td><span class="fw-bold text-gray-900 text-hover-primary">{{ $d->title }}</span></td>
                        <td><code class="fs-7">{{ $d->slug }}</code></td>
                        <td><span class="text-muted fs-7">{{ $d->parent_id ?? '—' }}</span></td>
                        <td>{{ $d->order }}</td>
                        <td>
                            @if($d->status)
                                <span class="badge badge-light-success">Ativo</span>
                            @else
                                <span class="badge badge-light-danger">Inativo</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-10">Sem documentos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection
