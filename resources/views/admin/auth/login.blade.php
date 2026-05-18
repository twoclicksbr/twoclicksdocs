@extends('admin.layouts.auth')

@section('content')
<div class="w-full max-w-sm bg-neutral-900 border border-neutral-800 rounded-lg p-6">
    <h1 class="text-xl font-bold mb-1">TwoClicks Admin</h1>
    <p class="text-sm text-gray-400 mb-6">Entre com seu email e senha</p>

    @if($errors->any())
        <div class="bg-red-900 border border-red-700 text-red-200 px-3 py-2 rounded mb-4 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login.attempt') }}" class="space-y-3">
        @csrf
        <div>
            <label class="block text-xs text-gray-400 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full bg-black border border-neutral-800 rounded px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1">Senha</label>
            <input type="password" name="password" required
                   class="w-full bg-black border border-neutral-800 rounded px-3 py-2 text-sm">
        </div>
        <label class="flex items-center gap-2 text-xs text-gray-400">
            <input type="checkbox" name="remember" value="1"> Manter conectado
        </label>
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-sm">
            Entrar
        </button>
    </form>
</div>
@endsection
