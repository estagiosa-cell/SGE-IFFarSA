// Este módulo fornece funções para mostrar e esconder um spinner de carregamento
// que pode ser usado para indicar que uma ação está em progresso, como o envio de um formulário.
function showSpinner() {
  const spinner = document.getElementById('loading-spinner');
  if (spinner) {
    spinner.classList.remove('d-none');
  }
}

function hideSpinner() {
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
    form.addEventListener('submit', function () {
      showSpinner();
    });
  });

  // Gatilho para qualquer elemento com a classe .spinner-trigger
  document.querySelectorAll('.spinner-trigger').forEach(el => {
    el.addEventListener('click', showSpinner);
  });
}