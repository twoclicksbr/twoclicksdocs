<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — TwoClicks Docs</title>
    <link rel="shortcut icon" href="{{ asset('metronic/media/logos/favicon.ico') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700">
    <link href="{{ asset('metronic/plugins/global/plugins.bundle.css') }}" rel="stylesheet">
    <link href="{{ asset('metronic/css/style.bundle.css') }}" rel="stylesheet">
    <style>
        #kt_app_content { margin-top: 0 !important; }

        /* task #26 — header sticky/fixed deve ser SÓLIDO (sem transparência ou
           backdrop-blur) para que conteúdo abaixo não vaze por trás dele ao
           rolar. Mantém a mesma cor base do Metronic (--bs-app-header-base-bg-color
           = #0B0C10) — fallback hardcoded caso a var não esteja disponível. */
        #kt_app_header.app-header {
            background-color: var(--bs-app-header-base-bg-color, #0B0C10) !important;
            opacity: 1 !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            z-index: 1000 !important;
        }
        [data-kt-app-header-sticky="on"] #kt_app_header.app-header {
            background-color: var(--bs-app-header-sticky-bg-color, #0B0C10) !important;
            opacity: 1 !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }
    </style>
    @stack('styles')
</head>
<body id="kt_app_body" data-kt-app-header-fixed="true" data-kt-app-header-fixed-mobile="true" class="app-default">
<x-environment-banner />
<script>
var defaultThemeMode = "dark";
var themeMode;
if (document.documentElement) {
    if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
        themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
    } else {
        if (localStorage.getItem("data-bs-theme") !== null) {
            themeMode = localStorage.getItem("data-bs-theme");
        } else {
            themeMode = defaultThemeMode;
        }
    }
    if (themeMode === "system") {
        themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
    }
    document.documentElement.setAttribute("data-bs-theme", themeMode);
}
</script>

<div class="d-flex flex-column flex-root app-root" id="kt_app_root">
    <div class="app-page flex-column flex-column-fluid" id="kt_app_page">

        {{-- Header --}}
        <div id="kt_app_header" class="app-header"
             data-kt-sticky="true"
             data-kt-sticky-activate="{default: false, lg: true}"
             data-kt-sticky-name="app-header-sticky"
             data-kt-sticky-offset="{default: false, lg: '300px'}">
            <div class="app-container container-fluid d-flex align-items-stretch justify-content-between" id="kt_app_header_container">

                {{-- Mobile toggle --}}
                <div class="d-flex align-items-center d-lg-none ms-n2 me-2">
                    <div class="btn btn-icon btn-active-color-primary w-35px h-35px" id="kt_app_header_menu_toggle">
                        <i class="ki-outline ki-abstract-14 fs-2"></i>
                    </div>
                </div>

                {{-- Logo --}}
                <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0 me-lg-15">
                    <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                        <span class="fw-bold fs-5 text-white">TwoClicks</span>
                        <span class="badge badge-sm badge-light-primary">Admin</span>
                    </a>
                </div>

                {{-- Header nav + right side --}}
                <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1" id="kt_app_header_wrapper">

                    {{-- Nav menu --}}
                    <div class="app-header-menu app-header-mobile-drawer align-items-stretch"
                         data-kt-drawer="true"
                         data-kt-drawer-name="app-header-menu"
                         data-kt-drawer-activate="{default: true, lg: false}"
                         data-kt-drawer-overlay="true"
                         data-kt-drawer-width="250px"
                         data-kt-drawer-direction="start"
                         data-kt-drawer-toggle="#kt_app_header_menu_toggle"
                         data-kt-swapper="true"
                         data-kt-swapper-mode="{default: 'append', lg: 'prepend'}"
                         data-kt-swapper-parent="{default: '#kt_app_body', lg: '#kt_app_header_wrapper'}">
                        @php $cr = request()->route()?->getName() ?? ''; @endphp
                        <div class="menu menu-rounded menu-active-bg menu-state-primary menu-column menu-lg-row menu-title-gray-700 menu-icon-gray-500 menu-arrow-gray-500 my-5 my-lg-0 align-items-stretch fw-semibold px-2 px-lg-0"
                             id="kt_app_header_menu" data-kt-menu="true">

                            {{-- Dashboard --}}
                            <div class="menu-item me-0 me-lg-2 {{ str_starts_with($cr, 'admin.dashboard') ? 'here' : '' }}">
                                <a class="menu-link" href="{{ route('admin.dashboard') }}">
                                    <span class="menu-title">Dashboard</span>
                                </a>
                            </div>

                            {{-- Projetos --}}
                            <div class="menu-item me-0 me-lg-2 {{ str_starts_with($cr, 'admin.projetos') ? 'here' : '' }}">
                                <a class="menu-link" href="{{ route('admin.projetos.index') }}">
                                    <span class="menu-title">Projetos</span>
                                </a>
                            </div>

                            {{-- Pessoas / Usuários --}}
                            <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                                 data-kt-menu-placement="bottom-start"
                                 class="menu-item menu-lg-down-accordion me-0 me-lg-2 {{ (str_starts_with($cr, 'admin.pessoas') || str_starts_with($cr, 'admin.usuarios')) ? 'here show' : '' }}">
                                <span class="menu-link">
                                    <span class="menu-title">Usuários</span>
                                    <span class="menu-arrow d-lg-none"></span>
                                </span>
                                <div class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown py-4 w-200px">
                                    <div class="menu-item">
                                        <a class="menu-link {{ str_starts_with($cr, 'admin.pessoas') ? 'active' : '' }}" href="{{ route('admin.pessoas.index') }}">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">Pessoas</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a class="menu-link {{ str_starts_with($cr, 'admin.usuarios') ? 'active' : '' }}" href="{{ route('admin.usuarios.index') }}">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">Usuários</span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            {{-- Tabelas auxiliares --}}
                            @php
                                $auxRoutes = ['admin.task-statuses', 'admin.task-fases', 'admin.task-modulos', 'admin.task-tipos', 'admin.task-prioridades'];
                                $auxActive = collect($auxRoutes)->some(fn($r) => str_starts_with($cr, $r));
                            @endphp
                            <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                                 data-kt-menu-placement="bottom-start"
                                 class="menu-item menu-lg-down-accordion me-0 me-lg-2 {{ $auxActive ? 'here show' : '' }}">
                                <span class="menu-link">
                                    <span class="menu-title">Auxiliares</span>
                                    <span class="menu-arrow d-lg-none"></span>
                                </span>
                                <div class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown py-4 w-200px">
                                    @foreach([
                                        'admin.task-statuses' => 'Status',
                                        'admin.task-fases' => 'Fases',
                                        'admin.task-modulos' => 'Módulos',
                                        'admin.task-tipos' => 'Tipos',
                                        'admin.task-prioridades' => 'Prioridades',
                                    ] as $r => $lbl)
                                    <div class="menu-item">
                                        <a class="menu-link {{ str_starts_with($cr, $r) ? 'active' : '' }}" href="{{ route("{$r}.index") }}">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">{{ $lbl }}</span>
                                        </a>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Documentos --}}
                            <div class="menu-item me-0 me-lg-2 {{ str_starts_with($cr, 'admin.documentos') ? 'here' : '' }}">
                                <a class="menu-link" href="{{ route('admin.documentos.index') }}">
                                    <span class="menu-title">Documentos</span>
                                </a>
                            </div>

                            {{-- Tarefas --}}
                            <div class="menu-item me-0 me-lg-2 {{ str_starts_with($cr, 'admin.tarefas') ? 'here' : '' }}">
                                <a class="menu-link" href="{{ route('admin.tarefas.index') }}">
                                    <span class="menu-title">Tarefas</span>
                                </a>
                            </div>

                            {{-- Auditoria --}}
                            <div class="menu-item me-0 me-lg-2 {{ str_starts_with($cr, 'admin.audit-logs') ? 'here' : '' }}">
                                <a class="menu-link" href="{{ route('admin.audit-logs.index') }}">
                                    <span class="menu-title">Auditoria</span>
                                </a>
                            </div>

                            {{-- Manutenção (só fora de produção — operação destrutiva no sandbox) --}}
                            @unless(app()->environment('production'))
                                <div class="menu-item me-0 me-lg-2 {{ str_starts_with($cr, 'admin.manutencao') ? 'here' : '' }}">
                                    <a class="menu-link" href="{{ route('admin.manutencao.index') }}">
                                        <span class="menu-title">Manutenção</span>
                                    </a>
                                </div>
                            @endunless

                        </div>
                    </div>
                    {{-- End nav --}}

                    {{-- Right side: project switcher + user menu --}}
                    <div class="app-navbar flex-shrink-0 d-flex align-items-center gap-2">

                        {{-- Project switcher --}}
                        @php $currentProject = \App\Services\ProjectContext::current(); @endphp
                        @if($currentProject)
                        <div class="app-navbar-item" id="kt_header_project_toggle">
                            <div class="d-flex align-items-center cursor-pointer gap-2 px-3 py-2 rounded"
                                 data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                                 data-kt-menu-attach="parent"
                                 data-kt-menu-placement="bottom-end">
                                <i class="ki-outline ki-abstract-26 fs-4 text-white opacity-75"></i>
                                <span class="text-white fw-semibold fs-7 d-none d-md-inline">{{ $currentProject->name }}</span>
                                <i class="ki-outline ki-down fs-8 text-white opacity-75"></i>
                            </div>
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-220px"
                                 data-kt-menu="true">
                                <div class="menu-item px-3">
                                    <div class="menu-content text-muted fw-semibold fs-7 px-3 pb-2">Trocar projeto</div>
                                </div>
                                @foreach(\App\Models\Project::where('status', true)->orderBy('name')->get() as $proj)
                                <div class="menu-item px-3">
                                    <form method="POST" action="{{ route('admin.switch-project') }}">
                                        @csrf
                                        <input type="hidden" name="project_id" value="{{ $proj->id }}">
                                        <button type="submit"
                                                class="menu-link px-3 w-100 text-start border-0 bg-transparent {{ $proj->id === $currentProject->id ? 'active' : '' }}">
                                            {{ $proj->name }}
                                            @if($proj->id === $currentProject->id)
                                                <i class="ki-outline ki-check fs-5 ms-auto text-success"></i>
                                            @endif
                                        </button>
                                    </form>
                                </div>
                                @endforeach
                                <div class="separator my-2"></div>
                                <div class="menu-item px-3">
                                    <a class="menu-link px-3" href="{{ route('admin.select-project') }}">
                                        <i class="ki-outline ki-grid fs-5 me-2"></i> Ver todos
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- User menu --}}
                        <div class="app-navbar-item ms-2" id="kt_header_user_menu_toggle">
                            <div class="d-flex align-items-center cursor-pointer gap-2"
                                 data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                                 data-kt-menu-attach="parent"
                                 data-kt-menu-placement="bottom-end">
                                <div class="symbol symbol-35px symbol-md-40px">
                                    <span class="symbol-label bg-light-primary text-primary fw-bold fs-6">
                                        {{ strtoupper(substr(auth()->user()?->first_name ?? 'A', 0, 1)) }}
                                    </span>
                                </div>
                                <div class="d-none d-md-flex flex-column align-items-start">
                                    <span class="text-white fw-semibold fs-7">{{ auth()->user()?->first_name }}</span>
                                </div>
                            </div>
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-250px"
                                 data-kt-menu="true">
                                <div class="menu-item px-3">
                                    <div class="menu-content d-flex align-items-center px-3">
                                        <div class="symbol symbol-50px me-5">
                                            <span class="symbol-label bg-light-primary text-primary fw-bold fs-4">
                                                {{ strtoupper(substr(auth()->user()?->first_name ?? 'A', 0, 1)) }}
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <div class="fw-bold fs-5">{{ auth()->user()?->first_name ?? '—' }}</div>
                                            <span class="text-muted fw-semibold fs-7">{{ auth()->user()?->email }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="separator my-2"></div>
                                <div class="menu-item px-5">
                                    <form method="POST" action="{{ route('admin.logout') }}">
                                        @csrf
                                        <button type="submit" class="menu-link px-5 w-100 text-start border-0 bg-transparent">
                                            Sair
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
        {{-- End header --}}

        <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">

            {{-- Toolbar --}}
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 my-0">
                            @yield('title', 'Admin')
                        </h1>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @yield('header-actions')
                    </div>
                </div>
            </div>
            {{-- End toolbar --}}

            {{-- Content --}}
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-fluid">

                    @if(session('success'))
                        <div class="alert alert-success d-flex align-items-center p-5 mb-5">
                            <i class="ki-outline ki-shield-tick fs-2hx text-success me-4"></i>
                            <div class="d-flex flex-column">
                                <span>{{ session('success') }}</span>
                            </div>
                            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                                <i class="ki-outline ki-cross fs-2 text-success"></i>
                            </button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
                            <i class="ki-outline ki-information-5 fs-2hx text-danger me-4"></i>
                            <div class="d-flex flex-column">
                                <span>{{ session('error') }}</span>
                            </div>
                            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                                <i class="ki-outline ki-cross fs-2 text-danger"></i>
                            </button>
                        </div>
                    @endif

                    @yield('content')

                </div>
            </div>
            {{-- End content --}}

        </div>
    </div>
</div>

<script src="{{ asset('metronic/plugins/global/plugins.bundle.js') }}"></script>
<script src="{{ asset('metronic/js/scripts.bundle.js') }}"></script>
@stack('scripts')
</body>
</html>
