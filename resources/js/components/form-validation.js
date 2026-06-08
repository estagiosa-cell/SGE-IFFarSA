export function initializeFormValidation() {
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
        // Remove 'is-invalid' ao interagir com o campo
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
        input.addEventListener('change', function() {
            this.classList.remove('is-invalid');
        });

        input.addEventListener('invalid', function(event) {
            // Força a exibição visual do erro (borda vermelha e texto) injetando a classe do Bootstrap
            event.target.classList.add('is-invalid');

            // Atualiza o texto dinamicamente de acordo com o motivo da falha (resolve campos required via JS)
            let feedbackDiv = event.target.closest('div').querySelector('.invalid-feedback');
            if (feedbackDiv) {
                if (event.target.validity.valueMissing) {
                    feedbackDiv.textContent = 'Este campo é obrigatório.';
                } else if (event.target.validity.typeMismatch || event.target.validity.patternMismatch) {
                    feedbackDiv.textContent = 'Formato inválido.';
                } else {
                    feedbackDiv.textContent = event.target.validationMessage || 'Campo inválido.';
                }
            }

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
}
