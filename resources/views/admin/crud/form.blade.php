@extends('admin.layouts.app')

@section('title', "{$mode} {$title}")
@section('header', ($mode === 'create' ? 'Novo' : 'Editar') . " {$title}")

@section('content')
<form method="POST" action="{{ $mode === 'create' ? route("{$route}.store") : route("{$route}.update", $item->id) }}" class="max-w-2xl">
    @csrf
    @if($mode === 'edit')
        @method('PUT')
    @endif

    @foreach($fields as $f)
        @if($f['in_form'] ?? true)
            <div class="mb-3">
                <label class="block text-xs text-gray-400 mb-1">{{ $f['label'] }}</label>
                @php $type = $f['type'] ?? 'text'; @endphp

                @if($type === 'textarea')
                    <textarea name="{{ $f['name'] }}" rows="6"
                              class="w-full bg-tc-dark border border-tc-border rounded px-3 py-2 text-sm">{{ old($f['name'], $item->{$f['name']}) }}</textarea>
                @elseif($type === 'boolean')
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="hidden" name="{{ $f['name'] }}" value="0">
                        <input type="checkbox" name="{{ $f['name'] }}" value="1"
                               {{ old($f['name'], $item->{$f['name']}) ? 'checked' : '' }}>
                        Ativo
                    </label>
                @elseif($type === 'select')
                    <select name="{{ $f['name'] }}" class="w-full bg-tc-dark border border-tc-border rounded px-3 py-2 text-sm">
                        <option value="">— selecione —</option>
                        @foreach(($options[$f['name']] ?? []) as $val => $lbl)
                            <option value="{{ $val }}" {{ (string) old($f['name'], $item->{$f['name']}) === (string) $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                @elseif($type === 'password')
                    <input type="password" name="{{ $f['name'] }}"
                           class="w-full bg-tc-dark border border-tc-border rounded px-3 py-2 text-sm">
                    @if($mode === 'edit')
                        <div class="text-xs text-gray-500 mt-1">Deixe em branco pra manter a senha atual.</div>
                    @endif
                @else
                    <input type="{{ $type }}" name="{{ $f['name'] }}"
                           value="{{ old($f['name'], $item->{$f['name']}) }}"
                           class="w-full bg-tc-dark border border-tc-border rounded px-3 py-2 text-sm">
                @endif

                @error($f['name'])
                    <div class="text-xs text-red-400 mt-1">{{ $message }}</div>
                @enderror
            </div>
        @endif
    @endforeach

    <div class="flex gap-2 mt-6">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-sm px-4 py-2 rounded">
            {{ $mode === 'create' ? 'Criar' : 'Salvar' }}
        </button>
        <a href="{{ route("{$route}.index") }}" class="text-sm px-4 py-2 rounded border border-tc-border hover:bg-tc-card">
            Cancelar
        </a>
    </div>
</form>
@endsection
