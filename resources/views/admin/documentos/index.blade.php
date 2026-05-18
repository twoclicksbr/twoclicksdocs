@extends('admin.layouts.app')

@section('title', 'Documentos')
@section('header', 'Documentos')

@section('content')

<form method="GET" class="mb-4">
    <label class="text-xs text-gray-400 mr-2">Projeto:</label>
    <select name="project_id" onchange="this.form.submit()" class="bg-tc-card border border-tc-border rounded px-3 py-1.5 text-sm">
        @foreach($projects as $p)
            <option value="{{ $p->id }}" {{ (string)$projectId === (string)$p->id ? 'selected' : '' }}>{{ $p->name }}</option>
        @endforeach
    </select>
</form>

<div class="bg-tc-card border border-tc-border rounded overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-tc-dark border-b border-tc-border text-xs uppercase text-gray-400">
            <tr>
                <th class="px-3 py-2 text-left">ID</th>
                <th class="px-3 py-2 text-left">Título</th>
                <th class="px-3 py-2 text-left">Slug</th>
                <th class="px-3 py-2 text-left">Pai</th>
                <th class="px-3 py-2 text-left">Ordem</th>
                <th class="px-3 py-2 text-left">Ativo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($docs as $d)
                <tr class="border-b border-tc-border last:border-0 hover:bg-tc-dark">
                    <td class="px-3 py-2 text-gray-400">{{ $d->id }}</td>
                    <td class="px-3 py-2 font-medium">{{ $d->title }}</td>
                    <td class="px-3 py-2 font-mono text-xs">{{ $d->slug }}</td>
                    <td class="px-3 py-2 text-gray-400">{{ $d->parent_id ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $d->order }}</td>
                    <td class="px-3 py-2">{{ $d->status ? '✓' : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-3 py-6 text-center text-gray-500">Sem documentos.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<p class="text-xs text-gray-500 mt-3">Edição completa de documentos (com blocos hierárquicos) virá em próxima iteração. Por enquanto use a API ou MCP.</p>

@endsection
