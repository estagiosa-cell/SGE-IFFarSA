@extends('layouts.guest')
@section('title', 'Login')
@section('main-content')
    <div class="text-center mb-4">
        <h3 class="card-title mb-3">Acesso ao Sistema</h3>
        <p class="text-muted">Faça login para acessar o SGE-IFFarSA</p>
    </div>
    {{-- Alerta de erro de credenciais --}}
    @if ($errors->has('login_error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div>
                    <strong>Erro de autenticação:</strong><br>
                    {{ $errors->first('login_error') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <form class="needs-validation" action="{{ route('store.login') }}" method="POST" novalidate>
        @csrf
        <div class="mb-3">
            <div class="form-floating">
                <input type="email" name="email" id="email" value="{{ old('email') }}"
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
            <div class="form-floating position-relative">
                <input type="password" name="password" id="password"
                    class="form-control @error('password') is-invalid @enderror" placeholder="Senha" required>
                <label for="password"><i class="bi bi-lock me-2"></i>Senha</label>
                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted" 
                    style="z-index: 10; padding: 0.375rem 0.75rem; margin-right: 0.5rem;"
                    onclick="togglePasswordVisibility('password', this)">
                    <i class="bi bi-eye"></i>
                </button>
                <div class="invalid-feedback">
                    @if ($errors->has('password'))
                        {{ $errors->first('password') }}
                    @else
                        O campo senha é obrigatório.
                    @endif
                </div>
            </div>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary w-100 btn-lg">
                <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
            </button>
        </div>

        <div class="text-center">
            <a href="{{ route('password.request') }}" class="text-decoration-none small fw-semibold">
                Esqueceu a Senha?
            </a>
        </div>
    </form>

    <script>
        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>
@endsection
