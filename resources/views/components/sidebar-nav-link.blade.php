@props([
    'icon' => '',
    'active' => false,
    'route' => null,
])

@php
    $isActive = $active;
    if ($route) {
        $isActive = request()->routeIs($route);

        // Para rotas de recurso (como admin.courses.index), também ativa nos sub-recursos
        if (!$isActive && str_contains($route, '.index')) {
            $routeBase = substr($route, 0, strrpos($route, '.index')) . '.*';
            $isActive = request()->routeIs($routeBase);
        }
    }
    $url = $route ? route($route) : '#';
@endphp

<li class="nav-item mb-2">
    <a class="nav-link {{ $isActive ? 'active' : 'text-dark bg-white nav-link-sidebar-inactive' }}"
        href="{{ $url }}">
        @if ($icon)
            <i class="bi bi-{{ $icon }} me-2"></i>
        @endif
        {{ $slot }}
    </a>
</li>
