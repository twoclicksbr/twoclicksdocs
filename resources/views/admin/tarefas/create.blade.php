@extends('admin.layouts.app')

@section('title', 'Nova Tarefa')

@section('content')

@if($errors->any())
    <div class="alert alert-danger d-flex align-items-start mb-5">
        <i class="ki-outline ki-information fs-2 text-danger me-3 mt-1"></i>
        <ul class="mb-0">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.tarefas.store') }}">
    @csrf

    <div class="row g-5">

        {{-- Coluna principal --}}
        <div class="col-lg-8">
            <div class="card mb-5">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title fw-bold">Dados da Tarefa</h3>
                </div>
                <div class="card-body pb-5">

                    {{-- Projeto --}}
                    <div class="mb-6">
                        <label class="form-label fw-semibold required">Projeto</label>
                        <select name="project_id" id="projectSelect" class="form-select form-select-solid @error('project_id') is-invalid @enderror">
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ old('project_id', $projectId) == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('project_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text text-muted fs-8">Ao trocar o projeto, as opções de Status, Fase, Módulo, Tipo e Prioridade serão recarregadas.</div>
                    </div>

                    {{-- Título --}}
                    <div class="mb-6">
                        <label class="form-label fw-semibold required">Título</label>
                        <input type="text" name="title" value="{{ old('title') }}" maxlength="255"
                               class="form-control form-control-solid @error('title') is-invalid @enderror">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Descrição --}}
                    <div class="mb-6">
                        <label class="form-label fw-semibold">
                            Descrição
                            <span class="text-muted fs-8 fw-normal ms-1">(suporta markdown)</span>
                        </label>
                        <textarea name="description" rows="10"
                                  class="form-control form-control-solid @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- Coluna lateral --}}
        <div class="col-lg-4">

            <div class="card mb-5">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title fw-bold">Classificação</h3>
                </div>
                <div class="card-body pb-5">

                    {{-- Status --}}
                    <div class="mb-5">
                        <label class="form-label fw-semibold required">Status</label>
                        <select name="task_status_id" id="sel_status"
                                class="form-select form-select-solid @error('task_status_id') is-invalid @enderror">
                            <option value="">— selecione —</option>
                            @foreach($aux['statuses'] as $s)
                                <option value="{{ $s->id }}" {{ old('task_status_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('task_status_id')
                            <div class="fv-plugins-message-container invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Fase --}}
                    <div class="mb-5">
                        <label class="form-label fw-semibold required">Fase</label>
                        <select name="task_fase_id" id="sel_fase"
                                class="form-select form-select-solid @error('task_fase_id') is-invalid @enderror">
                            <option value="">— selecione —</option>
                            @foreach($aux['fases'] as $s)
                                <option value="{{ $s->id }}" {{ old('task_fase_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('task_fase_id')
                            <div class="fv-plugins-message-container invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Módulo --}}
                    <div class="mb-5">
                        <label class="form-label fw-semibold required">Módulo</label>
                        <select name="task_modulo_id" id="sel_modulo"
                                class="form-select form-select-solid @error('task_modulo_id') is-invalid @enderror">
                            <option value="">— selecione —</option>
                            @foreach($aux['modulos'] as $s)
                                <option value="{{ $s->id }}" {{ old('task_modulo_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('task_modulo_id')
                            <div class="fv-plugins-message-container invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tipo --}}
                    <div class="mb-5">
                        <label class="form-label fw-semibold required">Tipo</label>
                        <select name="task_tipo_id" id="sel_tipo"
                                class="form-select form-select-solid @error('task_tipo_id') is-invalid @enderror">
                            <option value="">— selecione —</option>
                            @foreach($aux['tipos'] as $s)
                                <option value="{{ $s->id }}" {{ old('task_tipo_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('task_tipo_id')
                            <div class="fv-plugins-message-container invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Prioridade (lookup) --}}
                    <div class="mb-5">
                        <label class="form-label fw-semibold required">Nível de Prioridade</label>
                        <select name="task_prioridade_id" id="sel_prioridade"
                                class="form-select form-select-solid @error('task_prioridade_id') is-invalid @enderror">
                            <option value="">— selecione —</option>
                            @foreach($aux['prioridades'] as $s)
                                <option value="{{ $s->id }}" {{ old('task_prioridade_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('task_prioridade_id')
                            <div class="fv-plugins-message-container invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            <div class="card mb-5">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title fw-bold">Opções</h3>
                </div>
                <div class="card-body pb-5">

                    {{-- Ordem --}}
                    <div class="mb-5">
                        <label class="form-label fw-semibold">Ordem</label>
                        <input type="number" name="order" value="{{ old('order', 0) }}"
                               class="form-control form-control-solid">
                    </div>

                    {{-- Priority flag --}}
                    <div class="mb-5">
                        <label class="form-label fw-semibold d-block">Prioridade na fila</label>
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input type="hidden" name="priority_flag" value="0">
                            <input class="form-check-input" type="checkbox" name="priority_flag" value="1"
                                   id="priorityFlag" {{ old('priority_flag') ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold text-gray-700" for="priorityFlag">
                                Marcar como retrabalho / prioridade
                            </label>
                        </div>
                    </div>

                    {{-- Status ativo --}}
                    <div class="mb-5">
                        <label class="form-label fw-semibold d-block">Ativa</label>
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input type="hidden" name="status" value="0">
                            <input class="form-check-input" type="checkbox" name="status" value="1"
                                   id="taskStatus" {{ old('status', '1') ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold text-gray-700" for="taskStatus">
                                Ativa
                            </label>
                        </div>
                    </div>

                </div>
            </div>

            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="ki-outline ki-check fs-4"></i> Criar Tarefa
                </button>
                <a href="{{ route('admin.tarefas.index', $projectId ? ['project_id' => $projectId] : []) }}"
                   class="btn btn-light">Cancelar</a>
            </div>

        </div>
    </div>

</form>

@push('scripts')
<script>
(function () {
    var projectSelect = document.getElementById('projectSelect');

    var selects = {
        task_status_id:     document.getElementById('sel_status'),
        task_fase_id:       document.getElementById('sel_fase'),
        task_modulo_id:     document.getElementById('sel_modulo'),
        task_tipo_id:       document.getElementById('sel_tipo'),
        task_prioridade_id: document.getElementById('sel_prioridade'),
    };

    var keys = {
        task_status_id:     'statuses',
        task_fase_id:       'fases',
        task_modulo_id:     'modulos',
        task_tipo_id:       'tipos',
        task_prioridade_id: 'prioridades',
    };

    function populate(sel, items) {
        sel.innerHTML = '<option value="">— selecione —</option>';
        items.forEach(function (item) {
            var opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.name;
            sel.appendChild(opt);
        });
    }

    projectSelect.addEventListener('change', function () {
        var id = this.value;
        if (!id) return;
        fetch('/admin/api/projetos/' + id + '/auxiliares')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                Object.keys(selects).forEach(function (name) {
                    populate(selects[name], data[keys[name]] || []);
                });
            });
    });
})();
</script>
@endpush

@endsection
