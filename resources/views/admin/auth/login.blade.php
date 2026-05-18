@extends('admin.layouts.auth')

@section('content')
<div class="d-flex flex-column flex-lg-row flex-column-fluid">
    {{-- Left panel --}}
    <div class="d-flex flex-column flex-center w-lg-50 p-10 order-2 order-lg-1">
        <div class="d-flex justify-content-between flex-column-fluid flex-column w-100 mw-450px">

            <div class="py-20">
                <div class="text-start mb-10">
                    <h1 class="text-gray-900 mb-3 fs-3x">TwoClicks Admin</h1>
                    <div class="text-gray-500 fw-semibold fs-6">Entre com seu email e senha</div>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger mb-8">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('admin.login.attempt') }}" class="form w-100">
                    @csrf
                    <div class="fv-row mb-8">
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               placeholder="Email"
                               class="form-control form-control-solid">
                    </div>
                    <div class="fv-row mb-7">
                        <input type="password" name="password" required
                               placeholder="Senha"
                               class="form-control form-control-solid">
                    </div>
                    <div class="d-flex flex-stack flex-wrap gap-3 mb-10">
                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="remember" value="1">
                            <span class="form-check-label text-gray-700 fs-6">Manter conectado</span>
                        </label>
                    </div>
                    <div class="d-flex flex-stack">
                        <button type="submit" class="btn btn-primary me-2 flex-shrink-0">
                            Entrar
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    {{-- Right panel --}}
    <div class="d-none d-lg-flex flex-lg-row-fluid w-50 bgi-size-cover bgi-position-y-center bgi-position-x-start bgi-no-repeat order-1 order-lg-2"
         style="background: linear-gradient(135deg, #1e1e2d 0%, #2d2d44 100%);">
        <div class="d-flex flex-column flex-center py-15 px-5 px-md-15 w-100">
            <h3 class="fs-2qx fw-bold text-white text-center mb-7">TwoClicks Docs</h3>
            <div class="text-white fs-base text-center opacity-75">Gestão de projetos, documentação e tarefas</div>
        </div>
    </div>
</div>
@endsection
