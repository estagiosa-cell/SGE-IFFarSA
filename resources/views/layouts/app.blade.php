<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.svg') }}">
  <title>@yield('title') - SIGE-IFFarSA</title>
  @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>

<body>

  @if (session('message'))
    @php
      $toastClass = 'text-bg-' . session('messageType', 'warning');
    @endphp

    {{-- Toast do Bootstrap para exibir mensagens --}}
    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3">
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

  @yield('content')

  <x-spinner />
</body>

</html>
