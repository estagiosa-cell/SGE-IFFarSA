@extends('layouts.app')
@section('title', 'Welcome')
@section('content')
<div class="container-fluid vh-100">
  <div class="row h-100">
    <!-- Lado Esquerdo - Informações do Sistema -->
    <div class="d-none d-lg-flex col-lg-8 align-items-center bg-primary text-white">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-8">
            <div class="text-center mb-5">
              <img src="{{ asset('images/logo_iffar_c.png') }}" alt="Logo IFFar" class="img-fluid mb-4" style="max-width: 200px; filter: brightness(0) invert(1);">
              <h1 class="display-4 fw-bold mb-4">SIGE-IFFarSA</h1>
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
              <img src="{{ asset('images/logo_iffar_c.png') }}" alt="Logo IFFar" class="img-fluid mb-3" style="max-width: 120px;">
              <h4 class="fw-bold mb-2">SIGE-IFFarSA</h4>
              <p class="text-muted small mb-4">Sistema Integrado de Gestão de Estágios</p>
            </div>
            <div class="card shadow border-0">
              <div class="card-body p-3">
                <div class="text-center mb-4">
                  <h3 class="card-title mb-3">Acesso ao Sistema</h3>
                  <p class="text-muted">Faça login para acessar o SIGE-IFFarSA</p>
                </div>

                <form class="needs-validation" action="/login" method="POST" novalidate>
                  @csrf
                  <div class="mb-3">
                    <div class="form-floating">
                      <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="E-mail" required>
                      <label for="email"><i class="bi bi-envelope me-2"></i>E-mail</label>
                      <div class="invalid-feedback">
                        @if ($errors->has('email'))
                          {{ $errors->first('email') }}
                        @else
                          Por favor, insira seu e-mail.
                        @endif
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <div class="form-floating">
                      <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Senha" required>
                      <label for="password"><i class="bi bi-lock me-2"></i>Senha</label>
                      <div class="invalid-feedback">
                        @if ($errors->has('password'))
                          {{ $errors->first('password') }}
                        @else
                          Por favor, insira sua senha.
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
                    <a href="#" class="text-decoration-none small">
                      <i class="bi bi-question-circle me-1"></i>Esqueceu a senha?
                    </a>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
