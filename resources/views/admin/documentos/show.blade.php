@extends('admin.layouts.app')

@section('title', $document->title)

@section('header-actions')
    <a href="{{ route('admin.documentos.index', ['project_id' => $document->project_id]) }}"
       class="btn btn-light btn-sm">Voltar</a>
@endsection

@section('content')
<div class="row g-5">

    {{-- Sidebar: outros documentos do projeto --}}
    <div class="col-lg-3">
        <div class="card card-flush" style="max-height: calc(100vh - 220px); overflow-y: auto;">
            <div class="card-header pt-5">
                <div class="card-title">
                    <span class="fw-bold fs-7 text-muted text-uppercase">{{ $document->project->name }}</span>
                </div>
            </div>
            <div class="card-body py-2 px-3">
                @foreach($siblings as $s)
                    <a href="{{ route('admin.documentos.show', $s->id) }}"
                       class="d-flex align-items-center px-3 py-2 rounded fs-7 fw-semibold mb-1
                              {{ $s->id === $document->id ? 'bg-primary text-white' : 'text-gray-700 text-hover-primary bg-hover-light' }}">
                        {{ $s->parent_id ? '└ ' : '' }}{{ $s->title }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Main content --}}
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header border-0 pt-6">
                <div class="card-title flex-column">
                    <div class="d-flex align-items-center gap-3">
                        <code class="fs-7">{{ $document->slug }}</code>
                        @if($document->parent_id)
                            <span class="badge badge-light-secondary">Filho de #{{ $document->parent_id }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if(empty($tree))
                    <div class="text-muted">Sem conteúdo.</div>
                @else
                    <div class="d-flex flex-column gap-3">
                        @foreach($tree as $node)
                            @include('admin.documentos.partials.block', ['node' => $node, 'depth' => 0])
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
