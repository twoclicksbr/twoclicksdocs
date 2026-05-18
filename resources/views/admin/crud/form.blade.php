@extends('admin.layouts.app')

@section('title', ($mode === 'create' ? 'Novo' : 'Editar') . " {$title}")

@section('content')
<div class="card mw-600px">
    <div class="card-body">
        <form method="POST" action="{{ $mode === 'create' ? route("{$route}.store") : route("{$route}.update", $item->id) }}">
            @csrf
            @if($mode === 'edit')
                @method('PUT')
            @endif

            @foreach($fields as $f)
                @if($f['in_form'] ?? true)
                    @php $type = $f['type'] ?? 'text'; @endphp
                    <div class="mb-6">
                        <label class="form-label fw-semibold">{{ $f['label'] }}</label>

                        @if($type === 'textarea')
                            <textarea name="{{ $f['name'] }}" rows="6"
                                      class="form-control form-control-solid">{{ old($f['name'], $item->{$f['name']}) }}</textarea>
                        @elseif($type === 'boolean')
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input type="hidden" name="{{ $f['name'] }}" value="0">
                                <input class="form-check-input" type="checkbox" name="{{ $f['name'] }}" value="1"
                                       id="field_{{ $f['name'] }}"
                                       {{ old($f['name'], $item->{$f['name']}) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-gray-700" for="field_{{ $f['name'] }}">Ativo</label>
                            </div>
                        @elseif($type === 'select')
                            <select name="{{ $f['name'] }}" class="form-select form-select-solid">
                                <option value="">— selecione —</option>
                                @foreach(($options[$f['name']] ?? []) as $val => $lbl)
                                    <option value="{{ $val }}" {{ (string) old($f['name'], $item->{$f['name']}) === (string) $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        @elseif($type === 'password')
                            <input type="password" name="{{ $f['name'] }}"
                                   class="form-control form-control-solid">
                            @if($mode === 'edit')
                                <div class="form-text text-muted">Deixe em branco para manter a senha atual.</div>
                            @endif
                        @else
                            <input type="{{ $type }}" name="{{ $f['name'] }}"
                                   value="{{ old($f['name'], $item->{$f['name']}) }}"
                                   class="form-control form-control-solid">
                        @endif

                        @error($f['name'])
                            <div class="fv-plugins-message-container invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                @endif
            @endforeach

            <div class="d-flex gap-3 mt-8">
                <button type="submit" class="btn btn-primary">
                    {{ $mode === 'create' ? 'Criar' : 'Salvar' }}
                </button>
                <a href="{{ route("{$route}.index") }}" class="btn btn-light">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
