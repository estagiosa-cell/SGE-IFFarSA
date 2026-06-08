@props([
    'name',
    'label',
    'id' => null,
    'icon' => null,
    'feedback' => null,
])

@php
    $inputId = $id ?? $name;
@endphp

<div class="form-floating">
    <select name="{{ $name }}" 
            id="{{ $inputId }}" 
            {{ $attributes->merge(['class' => 'form-select ' . ($errors->has($name) ? 'is-invalid' : '')]) }}>
        {{ $slot }}
    </select>
    <label for="{{ $inputId }}">
        @if($icon)
            <i class="bi {{ $icon }} me-2"></i>
        @endif
        {{ rtrim($label, ' *') }}
        <span class="text-danger required-indicator d-none">*</span>
    </label>
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @else
        <div class="invalid-feedback">
            {{ $feedback ?? ($attributes->has('required') ? 'Este campo é obrigatório.' : 'Campo inválido.') }}
        </div>
    @enderror
</div>
