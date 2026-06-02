@extends('layouts.app')

@section('title', 'Erro 404')

@section('content')
    <div class="container min-vh-100 d-flex align-items-center py-5">
        <div class="row justify-content-center w-100">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-5 text-center">
                        <div class="display-4 fw-bold text-primary mb-3">404</div>
                        <h1 class="h5 mb-3">Pagina nao encontrada</h1>
                        <p class="text-muted mb-4">
                            A pagina que voce tentou acessar nao existe ou foi movida.
                        </p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Voltar</a>
                            <a href="{{ url('/') }}" class="btn btn-primary">Ir para inicio</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
