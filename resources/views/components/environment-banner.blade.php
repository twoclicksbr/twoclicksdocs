@php
    $url = config('app.url', '');
    $name = config('app.name', 'TwoClicks Docs');

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
    z-index: 999999;
    letter-spacing: 0.03em;
    font-family: Inter, sans-serif;
">{{ $text }}</div>
<div style="height: 32px;"></div>
