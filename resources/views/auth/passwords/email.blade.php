@extends('layouts.guest')
@section('title', 'Recuperar Senha')
@section('main-content')
    <div class="text-center mb-4">
        <h3 class="card-title mb-3">
            Recuperar Senha
        </h3>
        <p class="text-muted">Informe seu e-mail para receber o link de recuperação</p>
    </div>

    <!-- Alerta de sucesso -->
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div>{{ session('status') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <form class="needs-validation" action="{{ route('password.email') }}" method="POST" novalidate>
        @csrf
        <div class="mb-3">
            <div class="form-floating">
                <input type="email" name="email" id="email" value="{{ old('email') ?? request('email') }}"
                    class="form-control @error('email') is-invalid @enderror" placeholder="E-mail" required>
                <label for="email"><i class="bi bi-envelope me-2"></i>E-mail</label>
                <div class="invalid-feedback">
                    @if ($errors->has('email'))
                        {{ $errors->first('email') }}
                    @else
                        O campo e-mail é obrigatório.
                    @endif
                </div>
            </div>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary w-100 btn-lg">
                <i class="bi bi-send small me-2"></i>Enviar
            </button>
        </div>

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-decoration-none small fw-semibold">
                <i class="bi bi-arrow-left"></i> Voltar ao Login
            </a>
        </div>
    </form>
@endsection
