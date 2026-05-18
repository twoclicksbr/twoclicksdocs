@extends('admin.layouts.app')

@section('title', $project->name)
@section('header', $project->name)

@section('header-actions')
    <div class="flex gap-2">
        <a href="{{ route('admin.projetos.edit', $project->id) }}" class="text-sm px-3 py-1.5 rounded border border-tc-border hover:bg-tc-card">Editar dados</a>
        <a href="{{ route('admin.projetos.index') }}" class="text-sm px-3 py-1.5 rounded border border-tc-border hover:bg-tc-card">Voltar</a>
    </div>
@endsection

@section('content')

@if(session('new_token'))
    <div class="bg-yellow-900 border border-yellow-700 rounded p-4 mb-6">
        <div class="text-sm text-yellow-200 mb-2">Token criado. <strong>Copie agora</strong> — não será exibido de novo:</div>
        <div class="bg-black border border-yellow-700 rounded p-3 font-mono text-sm break-all">{{ session('new_token') }}</div>
    </div>
@endif

<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-tc-card border border-tc-border rounded p-4">
        <div class="text-xs uppercase text-gray-400">Slug</div>
        <div class="text-lg font-mono">{{ $project->slug }}</div>
    </div>
    <div class="bg-tc-card border border-tc-border rounded p-4">
        <div class="text-xs uppercase text-gray-400">Status</div>
        <div class="text-lg">{{ $project->status ? 'Ativo' : 'Inativo' }}</div>
    </div>
    <div class="bg-tc-card border border-tc-border rounded p-4">
        <div class="text-xs uppercase text-gray-400">Criado em</div>
        <div class="text-lg">{{ $project->created_at?->format('d/m/Y H:i') }}</div>
    </div>
</div>

<div class="bg-tc-card border border-tc-border rounded">
    <div class="flex items-center justify-between border-b border-tc-border px-4 py-3">
        <h2 class="font-bold">Tokens</h2>
        <form method="POST" action="{{ route('admin.projetos.tokens.store', $project->id) }}" class="flex gap-2">
            @csrf
            <input type="text" name="name" placeholder="Nome do token (ex: alex, claude, code)" required
                   class="bg-tc-dark border border-tc-border rounded px-3 py-1.5 text-sm w-72">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-sm px-3 py-1.5 rounded">
                + Gerar token
            </button>
        </form>
    </div>

    <table class="w-full text-sm">
        <thead class="bg-tc-dark border-b border-tc-border text-xs uppercase text-gray-400">
            <tr>
                <th class="px-3 py-2 text-left">ID</th>
                <th class="px-3 py-2 text-left">Nome</th>
                <th class="px-3 py-2 text-left">Criado em</th>
                <th class="px-3 py-2 text-left">Último uso</th>
                <th class="px-3 py-2 text-right">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tokens as $t)
                <tr class="border-b border-tc-border last:border-0 hover:bg-tc-dark">
                    <td class="px-3 py-2 text-gray-400">{{ $t->id }}</td>
                    <td class="px-3 py-2 font-mono">{{ $t->name }}</td>
                    <td class="px-3 py-2 text-xs text-gray-400">{{ $t->created_at?->format('d/m/Y H:i') }}</td>
                    <td class="px-3 py-2 text-xs text-gray-400">{{ $t->last_used_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td class="px-3 py-2 text-right">
                        <form action="{{ route('admin.projetos.tokens.destroy', [$project->id, $t->id]) }}" method="POST" class="inline" onsubmit="return confirm('Revogar este token? Quem estiver usando perde acesso.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-200 text-xs">Revogar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500">Nenhum token. Gere o primeiro acima.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
