@extends('admin.layouts.app')

@section('title', 'Editar Tarefa')

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

<form method="POST" action="{{ route('admin.tarefas.update', $task->id) }}">
    @csrf
    @method('PUT')

    <div class="row g-5">

        {{-- Coluna principal --}}
        <div class="col-lg-8">
            <div class="card mb-5">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title fw-bold">Dados da Tarefa</h3>
                </div>
                <div class="card-body pb-5">

                    {{-- Projeto (read-only visual) --}}
                    <div class="mb-6">
                        <label class="form-label fw-semibold">Projeto</label>
                        <select disabled class="form-select form-select-solid text-muted">
                            <option>{{ $task->project?->name ?? '—' }}</option>
                        </select>
                        <div class="form-text text-muted fs-8">O projeto não pode ser alterado após a criação.</div>
                    </div>

                    {{-- Título --}}
                    <div class="mb-6">
                        <label class="form-label fw-semibold required">Título</label>
                        <input type="text" name="title" value="{{ old('title', $task->title) }}" maxlength="255"
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
                                  class="form-control form-control-solid @error('description') is-invalid @enderror">{{ old('description', $task->description) }}</textarea>
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
                        <select name="task_status_id"
                                class="form-select form-select-solid @error('task_status_id') is-invalid @enderror">
                            <option value="">— selecione —</option>
                            @foreach($aux['statuses'] as $s)
                                <option value="{{ $s->id }}" {{ old('task_status_id', $task->task_status_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('task_status_id')
                            <div class="fv-plugins-message-container invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Fase --}}
                    <div class="mb-5">
                        <label class="form-label fw-semibold required">Fase</label>
                        <select name="task_fase_id"
                                class="form-select form-select-solid @error('task_fase_id') is-invalid @enderror">
                            <option value="">— selecione —</option>
                            @foreach($aux['fases'] as $s)
                                <option value="{{ $s->id }}" {{ old('task_fase_id', $task->task_fase_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('task_fase_id')
                            <div class="fv-plugins-message-container invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Módulo --}}
                    <div class="mb-5">
                        <label class="form-label fw-semibold required">Módulo</label>
                        <select name="task_modulo_id"
                                class="form-select form-select-solid @error('task_modulo_id') is-invalid @enderror">
                            <option value="">— selecione —</option>
                            @foreach($aux['modulos'] as $s)
                                <option value="{{ $s->id }}" {{ old('task_modulo_id', $task->task_modulo_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('task_modulo_id')
                            <div class="fv-plugins-message-container invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tipo --}}
                    <div class="mb-5">
                        <label class="form-label fw-semibold required">Tipo</label>
                        <select name="task_tipo_id"
                                class="form-select form-select-solid @error('task_tipo_id') is-invalid @enderror">
                            <option value="">— selecione —</option>
                            @foreach($aux['tipos'] as $s)
                                <option value="{{ $s->id }}" {{ old('task_tipo_id', $task->task_tipo_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('task_tipo_id')
                            <div class="fv-plugins-message-container invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Prioridade (lookup) --}}
                    <div class="mb-5">
                        <label class="form-label fw-semibold required">Nível de Prioridade</label>
                        <select name="task_prioridade_id"
                                class="form-select form-select-solid @error('task_prioridade_id') is-invalid @enderror">
                            <option value="">— selecione —</option>
                            @foreach($aux['prioridades'] as $s)
                                <option value="{{ $s->id }}" {{ old('task_prioridade_id', $task->task_prioridade_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('task_prioridade_id')
                            <div class="fv-plugins-message-container invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            @php
                $autoExecOptions = ($aux['statuses'] ?? collect())
                    ->filter(fn ($s) => $s->show_on_task);
                $currentAutoExec = $task->autoExecuteStatuses->pluck('id');
                $oldAutoExec = old('auto_execute_status_ids') !== null
                    ? collect((array) old('auto_execute_status_ids'))->map(fn ($id) => (int) $id)
                    : $currentAutoExec;
            @endphp
            @if($autoExecOptions->count() > 0)
                <div class="card mb-5">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title fw-bold">Auto-executar nos status</h3>
                    </div>
                    <div class="card-body pb-5">
                        <div class="text-muted fs-7 mb-3">
                            Marque os status que devem disparar webhook automático ao serem
                            atingidos por esta task.
                        </div>
                        @foreach($autoExecOptions as $s)
                            <div class="form-check form-check-custom form-check-solid mb-3">
                                <input class="form-check-input" type="checkbox"
                                       name="auto_execute_status_ids[]" value="{{ $s->id }}"
                                       id="autoExec_{{ $s->id }}"
                                       {{ $oldAutoExec->contains($s->id) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-gray-700"
                                       for="autoExec_{{ $s->id }}">
                                    {{ $s->name }}
                                    <span class="text-muted fs-8 ms-1">({{ $s->slug }})</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="card mb-5">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title fw-bold">Opções</h3>
                </div>
                <div class="card-body pb-5">

                    {{-- Ordem --}}
                    <div class="mb-5">
                        <label class="form-label fw-semibold">Ordem</label>
                        <input type="number" name="order" value="{{ old('order', $task->order) }}"
                               class="form-control form-control-solid">
                    </div>

                    {{-- Priority flag --}}
                    <div class="mb-5">
                        <label class="form-label fw-semibold d-block">Prioridade na fila</label>
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input type="hidden" name="priority_flag" value="0">
                            <input class="form-check-input" type="checkbox" name="priority_flag" value="1"
                                   id="priorityFlag" {{ old('priority_flag', $task->priority_flag) ? 'checked' : '' }}>
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
                                   id="taskStatus" {{ old('status', $task->status) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold text-gray-700" for="taskStatus">
                                Ativa
                            </label>
                        </div>
                    </div>

                </div>
            </div>

            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="ki-outline ki-check fs-4"></i> Salvar
                </button>
                <a href="{{ route('admin.tarefas.index') }}"
                   class="btn btn-light">Cancelar</a>
            </div>

        </div>
    </div>

</form>

@endsection
