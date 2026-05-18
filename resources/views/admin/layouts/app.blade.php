<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — TwoClicks Docs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'tc-dark': '#0a0a0a',
                        'tc-card': '#171717',
                        'tc-border': '#262626',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-tc-dark text-gray-100 min-h-screen">

<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside class="w-64 bg-tc-card border-r border-tc-border flex flex-col">
        <div class="p-4 border-b border-tc-border">
            <a href="{{ route('admin.dashboard') }}" class="text-lg font-bold">TwoClicks Admin</a>
        </div>

        <nav class="flex-1 overflow-y-auto py-2 text-sm">
            <div class="px-3 py-1 text-xs uppercase text-gray-500 mt-2">Geral</div>
            @include('admin.layouts.partials.nav-item', ['route' => 'admin.dashboard', 'label' => 'Dashboard'])
            @include('admin.layouts.partials.nav-item', ['route' => 'admin.projetos.index', 'label' => 'Projetos'])
            @include('admin.layouts.partials.nav-item', ['route' => 'admin.pessoas.index', 'label' => 'Pessoas'])
            @include('admin.layouts.partials.nav-item', ['route' => 'admin.usuarios.index', 'label' => 'Usuários'])

            <div class="px-3 py-1 text-xs uppercase text-gray-500 mt-4">Tabelas auxiliares</div>
            @include('admin.layouts.partials.nav-item', ['route' => 'admin.task-statuses.index', 'label' => 'Status de Tarefa'])
            @include('admin.layouts.partials.nav-item', ['route' => 'admin.task-fases.index', 'label' => 'Fases'])
            @include('admin.layouts.partials.nav-item', ['route' => 'admin.task-modulos.index', 'label' => 'Módulos'])
            @include('admin.layouts.partials.nav-item', ['route' => 'admin.task-tipos.index', 'label' => 'Tipos'])
            @include('admin.layouts.partials.nav-item', ['route' => 'admin.task-prioridades.index', 'label' => 'Prioridades'])

            <div class="px-3 py-1 text-xs uppercase text-gray-500 mt-4">Conteúdo</div>
            @include('admin.layouts.partials.nav-item', ['route' => 'admin.documentos.index', 'label' => 'Documentos'])
            @include('admin.layouts.partials.nav-item', ['route' => 'admin.tarefas.index', 'label' => 'Tarefas'])

            <div class="px-3 py-1 text-xs uppercase text-gray-500 mt-4">Sistema</div>
            @include('admin.layouts.partials.nav-item', ['route' => 'admin.audit-logs.index', 'label' => 'Auditoria'])
        </nav>

        <div class="p-3 border-t border-tc-border text-xs">
            <div class="text-gray-400 mb-2">{{ auth()->user()?->email ?? '' }}</div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="text-gray-400 hover:text-white">Sair</button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <main class="flex-1 overflow-y-auto">
        <header class="border-b border-tc-border px-6 py-3 flex items-center justify-between">
            <h1 class="text-lg font-bold">@yield('header', 'Admin')</h1>
            @yield('header-actions')
        </header>

        <div class="p-6">
            @if(session('success'))
                <div class="bg-green-900 border border-green-700 text-green-200 px-4 py-2 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-900 border border-red-700 text-red-200 px-4 py-2 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

@stack('scripts')
</body>
</html>
