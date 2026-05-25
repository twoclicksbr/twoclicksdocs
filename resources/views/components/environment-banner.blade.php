@php
    $url     = config('app.url', '');
    $appName = config('app.name', 'TwoClicks Docs');

    // task #83 — reaproveita a MESMA fonte do dropdown do topbar
    // (app.blade.php usa ProjectContext::current() para listar/exibir o
    // projeto ativo). Fallback para APP_NAME quando não houver projeto
    // na sessão (login, tela select-project, sessão expirada).
    $project = \App\Services\ProjectContext::current();
    $name    = $project?->name ?? $appName;

    if (app()->environment('local') || str_contains($url, '.test')) {
        $bg   = '#FBBF24';
        $text = "Localhost {$name}";
    } elseif (str_contains($url, 'sandbox')) {
        $bg   = '#DC2626';
        $text = "Sandbox {$name}";
    } else {
        $bg   = '#16A34A';
        $text = $name;
    }
@endphp
<style>
    /* task #83 — banner ocupa os 32px superiores; o header sticky/fixed
       do admin (#kt_app_header) precisa começar logo ABAIXO do banner,
       senão fica sobreposto. Sem efeito no layout `app-blank` (login),
       que não renderiza #kt_app_header. */
    #kt_app_header.app-header { top: 32px !important; }
</style>
<div style="
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 32px;
    background: {{ $bg }};
    color: #fff;
    font-weight: 700;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1100;
    letter-spacing: 0.03em;
    font-family: Inter, sans-serif;
">{{ $text }}</div>
<div style="height: 32px;"></div>
