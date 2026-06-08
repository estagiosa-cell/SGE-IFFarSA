@props([
    'id',
    'parentId' => null,
    'title',
    'icon' => null,
    'show' => false
])

<div class="accordion-item">
    <h2 class="accordion-header" id="heading{{ $id }}">
        <button class="accordion-button {{ $show ? '' : 'collapsed' }}" 
                type="button" 
                data-bs-toggle="collapse"
                data-bs-target="#collapse{{ $id }}" 
                aria-expanded="{{ $show ? 'true' : 'false' }}" 
                aria-controls="collapse{{ $id }}">
            @if($icon)
                <i class="bi {{ $icon }} me-2"></i>
            @endif
            {{ $title }}
        </button>
    </h2>
    <div id="collapse{{ $id }}" 
         class="accordion-collapse collapse {{ $show ? 'show' : '' }}" 
         aria-labelledby="heading{{ $id }}"
         @if($parentId) data-bs-parent="#{{ $parentId }}" @endif>
        <div class="accordion-body">
            {{ $slot }}
        </div>
    </div>
</div>

