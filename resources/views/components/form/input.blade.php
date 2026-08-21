@props([
    'name',
    'label',
    'id' => null,
    'type' => 'text',
    'value' => null,
    'icon' => null,
    'feedback' => null,
    'decimals' => null,
])

@php
    $inputId = $id ?? $name;
    $inputValue = old($name, $value);

    if ($decimals !== null && is_numeric($inputValue)) {
        $inputValue = number_format((float) $inputValue, $decimals, '.', '');
    }
@endphp

<div class="form-floating">
    <input type="{{ $type }}" 
           name="{{ $name }}" 
           id="{{ $inputId }}" 
           value="{{ $inputValue }}"
           {{ $attributes->merge(['class' => 'form-control ' . ($errors->has($name) ? 'is-invalid' : '')]) }}>
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
