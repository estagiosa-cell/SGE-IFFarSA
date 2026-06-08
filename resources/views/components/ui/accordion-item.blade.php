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

@once
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Abre o accordion se houver campos com erro de validação do backend (.is-invalid)
        let hasScrolledBackend = false;
        document.querySelectorAll('.accordion-collapse').forEach(function(collapse) {
            if (collapse.querySelector('.is-invalid')) {
                if (window.bootstrap && window.bootstrap.Collapse) {
                    let bsCollapse = new window.bootstrap.Collapse(collapse, { toggle: false });
                    bsCollapse.show();
                    
                    let button = document.querySelector('[data-bs-target="#' + collapse.id + '"]');
                    if (button) {
                        button.classList.remove('collapsed');
                        button.setAttribute('aria-expanded', 'true');
                    }
                }
            }
        });

        // Scroll para o primeiro erro de backend
        let firstInvalid = document.querySelector('.is-invalid');
        if (firstInvalid) {
            setTimeout(() => {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalid.focus();
            }, 300);
        }

        let hasScrolledFrontend = false;
        // Abre o accordion se a validação HTML5 do frontend falhar
        document.querySelectorAll('input, select, textarea').forEach(function(input) {
            input.addEventListener('invalid', function(event) {
                let collapse = event.target.closest('.accordion-collapse');
                if (collapse && !collapse.classList.contains('show')) {
                    if (window.bootstrap && window.bootstrap.Collapse) {
                        let bsCollapse = new window.bootstrap.Collapse(collapse, { toggle: false });
                        bsCollapse.show();
                        
                        let button = document.querySelector('[data-bs-target="#' + collapse.id + '"]');
                        if (button) {
                            button.classList.remove('collapsed');
                            button.setAttribute('aria-expanded', 'true');
                        }
                    }
                }

                if (!hasScrolledFrontend) {
                    hasScrolledFrontend = true;
                    setTimeout(() => {
                        event.target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        event.target.focus();
                        
                        // Reseta após um curto período para futuras submissões
                        setTimeout(() => { hasScrolledFrontend = false; }, 1000);
                    }, 300);
                }
            });
        });
    });
</script>
@endonce
