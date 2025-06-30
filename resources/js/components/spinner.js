// Este módulo fornece funções para mostrar e esconder um spinner de carregamento
// que pode ser usado para indicar que uma ação está em progresso, como o envio de um formulário.
export function showSpinner() {
  const spinner = document.getElementById('loading-spinner');
  if (spinner) {
    spinner.classList.remove('d-none');
  }
}

export function hideSpinner() {
  const spinner = document.getElementById('loading-spinner');
  if (spinner) {
    spinner.classList.add('d-none');
  }
}

// Esta função configura os "gatilhos" que mostram o spinner
export function initializeSpinner() {
  // Gatilho para formulários
  const forms = document.querySelectorAll('form');
  forms.forEach(form => {
    form.addEventListener('submit', function(event) {
      // Só mostra o spinner se o formulário for válido
      if (form.checkValidity()) {
        showSpinner();
      } else {
        // Se não for válido, previne o envio do formulário
        event.preventDefault();
        event.stopPropagation();
        form.classList.add('was-validated');
      }
    });
  });

  // Gatilho para qualquer elemento com a classe .spinner-trigger
  document.querySelectorAll('.spinner-trigger').forEach(el => {
    el.addEventListener('click', showSpinner);
  });
}