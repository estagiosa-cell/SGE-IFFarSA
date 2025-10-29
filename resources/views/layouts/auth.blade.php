@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-2 p-0">
                <x-sidebar />
            </div>

            <main class="col-lg-10 p-0 pb-3">
                {{-- Header para telas pequenas --}}
                <nav class="navbar navbar-expand-lg navbar-dark bg-primary d-lg-none shadow-sm sticky-top">
                    <div class="container-fluid">
                        <button class="navbar-toggler border-0 p-2" type="button" data-bs-toggle="offcanvas"
                            data-bs-target="#sidebarOffcanvas">
                            <i class="bi bi-layout-sidebar-inset fs-5"></i>
                        </button>
                        <span class="navbar-brand fw-bold fs-4 position-absolute start-50 translate-middle-x">
                            SGE-IFFarSA
                        </span>
                    </div>
                </nav>

                @yield('main-content')
            </main>
        </div>
    </div>
@endsection
