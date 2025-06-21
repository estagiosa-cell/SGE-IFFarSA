// função para inicializar o toast
export function initialize_toast() {
  const toast_message = document.getElementById('toast_message');
  if (toast_message) {
    const toast = new bootstrap.Toast(toast_message);
    toast.show();
  }
}