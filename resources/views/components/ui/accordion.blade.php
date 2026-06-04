@props(['id'])

<div class="accordion mb-4" id="{{ $id }}">
    {{ $slot }}
</div>
