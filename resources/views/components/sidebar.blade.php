<!-- Header para telas pequenas -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary d-lg-none shadow-sm">
  <div class="container-fluid">
    <button class="navbar-toggler border-0 p-2" type="button" data-bs-toggle="offcanvas"
      data-bs-target="#sidebarOffcanvas">
      <i class="bi bi-layout-sidebar-inset fs-5"></i>
    </button>
    <span class="navbar-brand fw-bold fs-4 position-absolute start-50 translate-middle-x">
      SIGE-IFFarSA
    </span>
  </div>
</nav>

<!-- Sidebar para telas grandes -->
<div class="d-none d-lg-flex bg-white flex-column min-vh-100 p-3 border-end col-lg-2">
  <h5 class="m-0">SIGE-IFFarSA</h5>
  <hr>
  <div class="flex-grow-1 overflow-y-auto overflow-x-hidden" style="max-height: calc(100vh - 200px);">
    <x-sidebar-nav />
  </div>
  <div class="mt-auto">
    <hr>
    <p class="text-muted d-block mb-2">
      <i class="bi bi-person-circle me-1"></i>
      {{ Auth::user()->name }}
    </p>
    <form action="{{ route('logout') }}" method="post">
      @csrf
      <button type="submit" class="btn btn-outline-danger w-100">
        <i class="bi bi-box-arrow-right"></i>
        Sair
      </button>
    </form>
  </div>
</div>

<!-- Offcanvas para telas pequenas -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarOffcanvas">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="sidebarOffcanvasLabel">SIGE-IFFarSA</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body d-flex flex-column">
    <div class="flex-grow-1 overflow-y-auto overflow-x-hidden">
      <x-sidebar-nav />
    </div>
    <div class="mt-auto">
      <hr>
      <p class="text-muted d-block mb-2">
        <i class="bi bi-person-circle me-1"></i>
        {{ Auth::user()->name }}
      </p>
      <form action="{{ route('logout') }}" method="post">
        @csrf
        <button type="submit" class="btn btn-outline-danger w-100">
          <i class="bi bi-box-arrow-right"></i>
          Sair
        </button>
      </form>
    </div>
  </div>
</div>
