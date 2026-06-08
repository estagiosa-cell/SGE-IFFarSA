import './bootstrap';
import * as bootstrap from 'bootstrap';
import.meta.glob([
  '../images/**'
]);

// Disponibiliza o bootstrap globalmente para ser usado por outros scripts
window.bootstrap = bootstrap;

import { initializeToast } from './components/toast.js';
import { initializeSpinner, showSpinner, hideSpinner } from './components/spinner.js';
import { initializeFormValidation } from './components/form-validation.js';

// Disponibiliza as funções do spinner globalmente
window.showSpinner = showSpinner;
window.hideSpinner = hideSpinner;

document.addEventListener('DOMContentLoaded', () => {
  // Inicializa os toasts
  initializeToast();
  
  // Inicializa o spinner
  initializeSpinner();

  // Inicializa a validação global de formulários
  initializeFormValidation();
});