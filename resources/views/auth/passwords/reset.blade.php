@extends('layouts.guest')
@section('title', 'Redefinir Senha')
@section('main-content')
    <div class="text-center mb-4">
        <h3 class="card-title mb-3">
            Redefinir Senha
        </h3>
        <p class="text-muted">Digite sua nova senha para concluir a redefinição</p>
    </div>

    {{-- Alerta de erro --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <form class="needs-validation" action="{{ route('password.update') }}" method="POST" novalidate>
        @csrf
        @method('PUT')
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <div class="form-floating">
                <input type="email" name="email" id="email" value="{{ request('email') ?? old('email') }}"
                    class="form-control @error('email') is-invalid @enderror" placeholder="E-mail" required readonly>
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
            <div class="form-floating">
                <input type="password" name="password" id="password"
                    class="form-control @error('password') is-invalid @enderror" placeholder="Nova Senha" maxlength="64"
                    required>
                <label for="password"><i class="bi bi-lock me-2"></i>Nova Senha</label>
                <div class="invalid-feedback">
                    @if ($errors->has('password'))
                        {{ $errors->first('password') }}
                    @else
                        O campo nova senha é obrigatório.
                    @endif
                </div>
            </div>
        </div>

        <div class="mb-3">
            <div class="form-floating">
                <input type="password" name="password_confirmation" id="password_confirmation"
                    class="form-control @error('password_confirmation') is-invalid @enderror"
                    placeholder="Confirmar Nova Senha" maxlength="64" required>
                <label for="password_confirmation"><i class="bi bi-lock-fill me-2"></i>Confirmar Nova Senha</label>
                <div class="invalid-feedback">
                    @if ($errors->has('password_confirmation'))
                        {{ $errors->first('password_confirmation') }}
                    @else
                        O campo confirmação de senha é obrigatório.
                    @endif
                </div>
            </div>
            <div class="mt-1">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Requisitos da senha:
                </small>
                <ul class="mb-0 mt-1 small">
                    <li class="text-muted">De 8 a 64 caracteres</li>
                    <li class="text-muted">Pelo menos uma letra maiúscula e uma minúscula</li>
                    <li class="text-muted">Pelo menos um número</li>
                </ul>
            </div>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary w-100 btn-lg">
                <i class="bi bi-check-circle small me-2"></i>Redefinir Senha
            </button>
        </div>

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-decoration-none small fw-semibold">
                <i class="bi bi-arrow-left"></i> Voltar ao Login
            </a>
        </div>
    </form>
@endsection
