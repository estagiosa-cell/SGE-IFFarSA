@props([
    'icon' => '',
    'active' => false,
    'route' => null,
])

@php
  $isActive = $active || ($route && request()->routeIs($route));
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
