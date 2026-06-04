@props([
    'name',
    'label',
    'id' => null,
    'value' => '1',
    'checked' => false,
])

@php
    $inputId = $id ?? $name;
@endphp

<div class="form-check form-switch">
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" 
           name="{{ $name }}" 
           id="{{ $inputId }}" 
           value="{{ $value }}"
           {{ $checked ? 'checked' : '' }}
           {{ $attributes->merge(['class' => 'form-check-input ' . ($errors->has($name) ? 'is-invalid' : '')]) }}>
    <label class="form-check-label" for="{{ $inputId }}">
        <strong>{{ $label }}</strong>
    </label>
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
