@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row g-5 mb-5">
    @foreach([
        'documents'    => ['label' => 'Documentos',          'icon' => 'ki-document',      'color' => 'warning'],
        'blocks'       => ['label' => 'Blocos',              'icon' => 'ki-abstract-14',   'color' => 'danger'],
        'tasks'        => ['label' => 'Tarefas',             'icon' => 'ki-check-square',  'color' => 'primary'],
        'task_details' => ['label' => 'Ciclos de execução',  'icon' => 'ki-timer',         'color' => 'success'],
    ] as $key => $cfg)
    <div class="col-6 col-md-3">
        <div class="card card-flush h-md-50 mb-5">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $stats[$key] }}</span>
                    <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ $cfg['label'] }}</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                        <i class="ki-outline {{ $cfg['icon'] }} fs-2 text-{{ $cfg['color'] }}"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
