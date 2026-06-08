@props([
    'name',
    'label',
    'id' => null,
    'value' => null,
    'icon' => null,
    'feedback' => null,
])

@php
    $inputId = $id ?? $name;
@endphp

<div class="form-floating">
    <textarea name="{{ $name }}" 
              id="{{ $inputId }}" 
              {{ $attributes->merge(['class' => 'form-control ' . ($errors->has($name) ? 'is-invalid' : '')]) }}>{{ old($name, $value ?? $slot) }}</textarea>
    <label for="{{ $inputId }}">
        @if($icon)
            <i class="bi {{ $icon }} me-2"></i>
        @endif
        {{ $label }}
    </label>
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @else
        @if($feedback)
            <div class="invalid-feedback">{{ $feedback }}</div>
        @endif
    @enderror
</div>
