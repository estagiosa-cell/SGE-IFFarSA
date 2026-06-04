@props([
    'icon' => 'bi-search',
    'title',
    'description',
])

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi {{ $icon }} text-muted display-3"></i>
            </div>
            <h4 class="text-muted mb-3">{{ $title }}</h4>
            <p class="text-muted mb-4">
                {!! $description !!}
            </p>
            @if($slot->isNotEmpty())
                {{ $slot }}
            @endif
        </div>
    </div>
</div>
