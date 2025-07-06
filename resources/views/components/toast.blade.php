@if (session('message'))
  @php
    // Define a classe do toast com base no tipo de mensagem
    // Se não houver tipo definido, usa 'warning' como padrão
    $toastClass = 'text-bg-' . session('messageType', 'warning');
  @endphp
  {{-- Toast do Bootstrap para exibir mensagens --}}
  <div class="toast-container position-fixed top-0 end-0 m-3 shadow-lg">
    <div id="toastMessage" class="toast align-items-center {{ $toastClass }} border-0" role="alert"
      aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">
          {{ session('message') }}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
          aria-label="Close"></button>
      </div>
    </div>
  </div>
@endif
