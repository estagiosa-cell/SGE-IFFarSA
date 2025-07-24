@extends('layouts.app')

@section('content')
    <div class="container-fluid vh-100">
        <div class="row h-100">
            <!-- Lado Esquerdo - Informações do Sistema -->
            <div class="d-none d-lg-flex col-lg-8 align-items-center bg-primary text-white">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="text-center mb-5">
                                <img src="{{ Vite::asset('resources/images/logo_iffar_c.png') }}" alt="Logo IFFar"
                                    class="img-fluid mb-4" style="max-width: 200px; filter: brightness(0) invert(1);">
                                <h1 class="display-4 fw-bold mb-4">SGE-IFFarSA</h1>
                                <h2 class="h4 mb-4">Sistema Integrado de Gestão de Estágios</h2>
                                <p class="lead">Instituto Federal Farroupilha - Campus Santo Augusto</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lado Direito - Formulário de Login -->
            <div class="col-12 col-lg-4 d-flex align-items-center">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-10 col-lg-12">
                            <!-- Logo para telas pequenas/médias -->
                            <div class="text-center mb-4 d-lg-none">
                                <img src="{{ Vite::asset('resources/images/logo_iffar_c.png') }}" alt="Logo IFFar"
                                    class="img-fluid mb-3" style="max-width: 120px;">
                                <h4 class="fw-bold mb-2">SGE-IFFarSA</h4>
                                <p class="text-muted small mb-4">Sistema Integrado de Gestão de Estágios</p>
                            </div>
                            <div class="card shadow border-0">
                                <div class="card-body p-3">
                                    @yield('main-content')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
