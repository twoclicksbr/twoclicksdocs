@extends('admin.layouts.app')

@section('title', 'Auditoria')
@section('header', 'Auditoria')

@section('content')

<form method="GET" class="flex gap-2 mb-4 text-sm">
    <select name="project_id" class="bg-tc-card border border-tc-border rounded px-3 py-1.5">
        <option value="">Todos projetos</option>
        @foreach($projects as $p)
            <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
        @endforeach
    </select>
    <select name="action" class="bg-tc-card border border-tc-border rounded px-3 py-1.5">
        <option value="">Todas ações</option>
        @foreach(['create', 'update', 'delete', 'restore', 'force_delete'] as $a)
            <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ $a }}</option>
        @endforeach
    </select>
    <input type="text" name="table_name" value="{{ request('table_name') }}" placeholder="Tabela"
           class="bg-tc-card border border-tc-border rounded px-3 py-1.5">
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded">Filtrar</button>
    <a href="{{ route('admin.audit-logs.index') }}" class="px-3 py-1.5 rounded border border-tc-border hover:bg-tc-card">Limpar</a>
</form>

<div class="bg-tc-card border border-tc-border rounded overflow-hidden">
    <table class="w-full text-xs">
        <thead class="bg-tc-dark border-b border-tc-border uppercase text-gray-400">
            <tr>
                <th class="px-3 py-2 text-left">Quando</th>
                <th class="px-3 py-2 text-left">Pessoa</th>
                <th class="px-3 py-2 text-left">Token</th>
                <th class="px-3 py-2 text-left">Projeto</th>
                <th class="px-3 py-2 text-left">Ação</th>
                <th class="px-3 py-2 text-left">Tabela</th>
                <th class="px-3 py-2 text-left">Registro</th>
                <th class="px-3 py-2 text-left">IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $l)
                <tr class="border-b border-tc-border last:border-0 hover:bg-tc-dark">
                    <td class="px-3 py-2 text-gray-400 whitespace-nowrap">{{ $l->created_at?->format('d/m H:i:s') }}</td>
                    <td class="px-3 py-2">{{ $l->person?->first_name ?? '—' }}</td>
                    <td class="px-3 py-2 font-mono text-gray-400">{{ $l->token_name ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $l->project?->name ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $l->action }}</td>
                    <td class="px-3 py-2 font-mono text-gray-400">{{ $l->table_name }}</td>
                    <td class="px-3 py-2 text-gray-400">#{{ $l->record_id }}</td>
                    <td class="px-3 py-2 text-gray-500">{{ $l->ip_address ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-3 py-6 text-center text-gray-500">Sem registros.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $logs->links() }}</div>

@endsection
