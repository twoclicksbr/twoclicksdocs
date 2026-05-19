@extends('admin.layouts.auth')

@section('content')
<div class="d-flex flex-column flex-root min-vh-100">
    <div class="d-flex flex-column flex-center flex-column-fluid p-10">

        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between w-100 mw-700px mb-10">
            <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                <span class="fw-bold fs-4 text-gray-900">TwoClicks</span>
                <span class="badge badge-sm badge-light-primary">Admin</span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted fw-semibold fs-7">{{ auth()->user()?->first_name }}</span>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-light btn-sm">Sair</button>
                </form>
            </div>
        </div>

        {{-- Card --}}
        <div class="w-100 mw-700px">
            <div class="text-center mb-10">
                <h1 class="text-gray-900 fw-bold fs-2">Selecione um projeto</h1>
                <p class="text-muted fw-semibold fs-6">Escolha o workspace em que deseja trabalhar agora.</p>
            </div>

            @if($projects->isEmpty())
                <div class="text-center text-muted py-10">
                    <i class="ki-outline ki-abstract-26 fs-3x mb-4 d-block text-gray-400"></i>
                    Nenhum projeto ativo disponível.
                </div>
            @else
                <div class="row g-4">
                    @foreach($projects as $project)
                    <div class="col-12 col-md-6">
                        <form method="POST" action="{{ route('admin.select-project.store') }}">
                            @csrf
                            <input type="hidden" name="project_id" value="{{ $project->id }}">
                            <button type="submit" class="btn w-100 p-0 text-start border-0 bg-transparent">
                                <div class="card card-flush h-100 hover-elevate-up cursor-pointer">
                                    <div class="card-body d-flex align-items-center gap-4 p-6">
                                        <div class="symbol symbol-50px flex-shrink-0">
                                            <span class="symbol-label bg-light-primary text-primary fw-bold fs-4">
                                                {{ strtoupper(substr($project->name, 0, 1)) }}
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <span class="fw-bold text-gray-900 fs-5 d-block text-truncate">{{ $project->name }}</span>
                                            <span class="text-muted fw-semibold fs-7">{{ $project->slug }}</span>
                                        </div>
                                        <i class="ki-outline ki-arrow-right fs-3 text-gray-400 flex-shrink-0"></i>
                                    </div>
                                </div>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
