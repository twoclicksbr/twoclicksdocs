@extends('admin.layouts.app')

@section('title', $titlePlural)
@section('header', $titlePlural)

@section('header-actions')
    <a href="{{ route("{$route}.create") }}" class="bg-blue-600 hover:bg-blue-700 text-sm px-3 py-1.5 rounded">
        + Novo
    </a>
@endsection

@section('content')

<form method="GET" class="mb-4">
    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar..."
           class="bg-tc-card border border-tc-border rounded px-3 py-1.5 text-sm w-64">
</form>

<div class="bg-tc-card border border-tc-border rounded overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-tc-dark border-b border-tc-border text-xs uppercase text-gray-400">
            <tr>
                <th class="px-3 py-2 text-left">ID</th>
                @foreach($fields as $f)
                    @if($f['in_table'] ?? true)
                        <th class="px-3 py-2 text-left">{{ $f['label'] }}</th>
                    @endif
                @endforeach
                <th class="px-3 py-2 text-right">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr class="border-b border-tc-border last:border-0 hover:bg-tc-dark">
                    <td class="px-3 py-2 text-gray-400">{{ $item->id }}</td>
                    @foreach($fields as $f)
                        @if($f['in_table'] ?? true)
                            <td class="px-3 py-2">
                                @if(($f['type'] ?? 'text') === 'boolean')
                                    {{ $item->{$f['name']} ? '✓' : '—' }}
                                @else
                                    {{ \Illuminate\Support\Str::limit((string) ($item->{$f['name']} ?? ''), 60) }}
                                @endif
                            </td>
                        @endif
                    @endforeach
                    <td class="px-3 py-2 text-right">
                        <a href="{{ route("{$route}.edit", $item->id) }}" class="text-blue-400 hover:text-blue-200 text-xs">Editar</a>
                        <form action="{{ route("{$route}.destroy", $item->id) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Confirma a exclusão?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-200 text-xs">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="99" class="px-3 py-6 text-center text-gray-500">Nenhum registro.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $items->links() }}
</div>
@endsection
