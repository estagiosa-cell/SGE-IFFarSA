import './bootstrap';
import * as bootstrap from 'bootstrap';

// Disponibiliza o bootstrap globalmente para ser usado por outros scripts
window.bootstrap = bootstrap;

import { initializeToast } from './components/toast.js';

document.addEventListener('DOMContentLoaded', () => {
  // Inicializa os toasts
  initializeToast();

});