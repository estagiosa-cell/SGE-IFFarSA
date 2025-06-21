// função para inicializar o toast
export function initializeToast() {
  const toastMessage = document.getElementById('toastMessage');
  if (toastMessage) {
    const toast = new bootstrap.Toast(toastMessage);
    toast.show();
  }
}