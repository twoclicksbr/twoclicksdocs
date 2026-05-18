<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — TwoClicks Admin</title>
    <link rel="shortcut icon" href="{{ asset('metronic/media/logos/favicon.ico') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700">
    <link href="{{ asset('metronic/plugins/global/plugins.bundle.css') }}" rel="stylesheet">
    <link href="{{ asset('metronic/css/style.bundle.css') }}" rel="stylesheet">
</head>
<body id="kt_body" class="app-blank">

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

<div class="d-flex flex-column flex-root" id="kt_app_root">
    @yield('content')
</div>

<script src="{{ asset('metronic/plugins/global/plugins.bundle.js') }}"></script>
<script src="{{ asset('metronic/js/scripts.bundle.js') }}"></script>
</body>
</html>
