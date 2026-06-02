{{-- Sidebar para telas grandes --}}
<div class="d-none d-lg-flex bg-white flex-column vh-100 p-2 border-end position-sticky top-0">
    <h5 class="m-2 d-flex align-items-center">
        <img src="{{ Vite::asset('resources/images/favicon.svg') }}" alt="Logo IFFarSA" class="me-2" style="height: 24px; width: auto;">
        SGE-IFFarSA
    </h5>
    <hr>
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

{{-- Offcanvas para telas pequenas --}}
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarOffcanvas">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title d-flex align-items-center" id="sidebarOffcanvasLabel">
            <img src="{{ Vite::asset('resources/images/favicon.svg') }}" alt="Logo IFFarSA" class="me-2" style="height: 24px; width: auto;">
            SGE-IFFarSA
        </h5>
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
