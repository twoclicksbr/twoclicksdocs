@extends('admin.layouts.app')

@section('title', $document->title)

@section('header-actions')
    <a href="{{ route('admin.documentos.index', ['project_id' => $document->project_id]) }}"
       class="btn btn-light btn-sm">Voltar</a>
@endsection

@push('styles')
<style>
.document-block { line-height: 1.7; color: var(--bs-gray-800); }
.document-block h1, .document-block h2, .document-block h3,
.document-block h4, .document-block h5, .document-block h6 {
    margin-top: 1.5rem; margin-bottom: .5rem; font-weight: 600; color: var(--bs-gray-900);
}
.document-block h1 { font-size: 1.5rem; }
.document-block h2 { font-size: 1.25rem; border-bottom: 1px solid var(--bs-gray-200); padding-bottom: .35rem; }
.document-block h3 { font-size: 1.1rem; }
.document-block p  { margin-bottom: .75rem; }
.document-block ul, .document-block ol { padding-left: 1.5rem; margin-bottom: .75rem; }
.document-block li { margin-bottom: .25rem; }
.document-block code {
    background: var(--bs-gray-100); color: var(--bs-danger);
    padding: .15em .4em; border-radius: 4px; font-size: .875em;
}
.document-block pre {
    background: #1e1e2e; color: #cdd6f4; border-radius: 8px;
    padding: 1rem 1.25rem; overflow-x: auto; margin-bottom: 1rem;
}
.document-block pre code { background: none; color: inherit; padding: 0; font-size: .85em; }
.document-block blockquote {
    border-left: 4px solid var(--bs-primary); background: var(--bs-light);
    margin: .75rem 0; padding: .6rem 1rem; border-radius: 0 6px 6px 0; color: var(--bs-gray-700);
}
.document-block blockquote p { margin-bottom: 0; }
.document-block hr { border-color: var(--bs-gray-200); margin: 1.5rem 0; }
.document-block table { width: 100%; }
.document-block table th { background: var(--bs-gray-100); font-weight: 600; }
.document-block a { color: var(--bs-primary); }
</style>
@endpush

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
                    <div class="d-flex flex-column gap-4">
                        @foreach($tree as $node)
                            @include('admin.documentos.partials.block', ['node' => $node, 'depth' => 0])
                        @endforeach
                    </div>
                @endif

                @if($childDocuments->isNotEmpty())
                    <div class="mt-6 pt-5 border-top">
                        <h3 class="fs-5 fw-bold mb-4 text-gray-700">Nesta seção</h3>
                        <div class="d-flex flex-column gap-2">
                            @foreach($childDocuments as $child)
                                <a href="{{ route('admin.documentos.show', $child->id) }}"
                                   class="d-flex align-items-center gap-2 text-gray-700 text-hover-primary fs-6 fw-semibold py-1">
                                    <i class="ki-outline ki-document fs-5 text-muted"></i>
                                    {{ $child->title }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
