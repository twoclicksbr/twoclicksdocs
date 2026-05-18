@extends('admin.layouts.app')

@section('title', $document->title)
@section('header', $document->title)

@section('header-actions')
    <a href="{{ route('admin.documentos.index', ['project_id' => $document->project_id]) }}"
       class="text-sm px-3 py-1.5 rounded border border-tc-border hover:bg-tc-card">
        Voltar
    </a>
@endsection

@section('content')

<div class="grid grid-cols-12 gap-4">

    {{-- Sidebar com outros documentos do mesmo projeto --}}
    <aside class="col-span-3 bg-tc-card border border-tc-border rounded p-3 max-h-[calc(100vh-180px)] overflow-y-auto">
        <div class="text-xs uppercase text-gray-400 mb-2 px-1">{{ $document->project->name }}</div>
        @foreach($siblings as $s)
            <a href="{{ route('admin.documentos.show', $s->id) }}"
               class="block px-2 py-1 text-sm rounded {{ $s->id === $document->id ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-tc-dark' }}">
                {{ $s->parent_id ? '└ ' : '' }}{{ $s->title }}
            </a>
        @endforeach
    </aside>

    {{-- Conteúdo principal --}}
    <main class="col-span-9 bg-tc-card border border-tc-border rounded p-6">

        <div class="mb-4 pb-4 border-b border-tc-border">
            <div class="text-xs uppercase text-gray-400">Slug</div>
            <div class="font-mono text-sm">{{ $document->slug }}</div>
            @if($document->parent_id)
                <div class="text-xs text-gray-500 mt-1">Filho do documento #{{ $document->parent_id }}</div>
            @endif
        </div>

        @if(empty($tree))
            <div class="text-gray-500 text-sm">Sem conteúdo.</div>
        @else
            <div class="space-y-3">
                @foreach($tree as $node)
                    @include('admin.documentos.partials.block', ['node' => $node, 'depth' => 0])
                @endforeach
            </div>
        @endif

    </main>
</div>

@endsection
