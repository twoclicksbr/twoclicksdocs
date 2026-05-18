@php
    $isActive = false;
    try {
        $currentRoute = request()->route()->getName();
        $isActive = str_starts_with($currentRoute ?? '', explode('.index', $route)[0]);
    } catch (\Throwable $e) {}
@endphp
<a href="{{ Route::has($route) ? route($route) : '#' }}"
   class="block px-3 py-2 hover:bg-tc-dark {{ $isActive ? 'bg-tc-dark text-white border-l-2 border-blue-500' : 'text-gray-300' }}">
    {{ $label }}
</a>
