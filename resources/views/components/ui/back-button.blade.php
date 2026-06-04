@props([
    'url',
    'text' => 'Voltar',
    'icon' => 'bi-arrow-left-circle'
])

<a href="{{ $url }}" {{ $attributes->merge(['class' => 'btn btn-outline-secondary']) }}>
    @if($icon)
        <i class="bi {{ $icon }} me-2"></i>
    @endif
    {{ $text }}
</a>
