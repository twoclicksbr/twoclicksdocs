@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row g-5 mb-5">
    <div class="col-6 col-md-6">
        <a href="{{ route('admin.documentos.index') }}" class="text-decoration-none">
            <div class="card card-flush h-md-50 mb-5">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $stats['documents'] }}</span>
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">Documentos</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0">
                    <div class="d-flex align-items-center flex-column mt-3 w-100">
                        <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                            <i class="ki-outline ki-document fs-2 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-6">
        <a href="{{ route('admin.tarefas.index') }}" class="text-decoration-none">
            <div class="card card-flush h-md-50 mb-5">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $stats['tasks'] }}</span>
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">Tarefas</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0">
                    <div class="d-flex align-items-center flex-column mt-3 w-100">
                        <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                            <i class="ki-outline ki-check-square fs-2 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection
