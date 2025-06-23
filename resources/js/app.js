import './bootstrap';
import * as bootstrap from 'bootstrap';

// Disponibiliza o bootstrap globalmente para ser usado por outros scripts
window.bootstrap = bootstrap;

import { initializeToast } from './components/toast.js';
import { initializeSpinner, showSpinner, hideSpinner } from './components/spinner.js';

// Disponibiliza as funções do spinner globalmente
window.showSpinner = showSpinner;
window.hideSpinner = hideSpinner;

document.addEventListener('DOMContentLoaded', () => {
  // Inicializa os toasts
  initializeToast();
  
  // Inicializa o spinner
  initializeSpinner();
});