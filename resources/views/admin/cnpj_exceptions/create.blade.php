@extends('layouts.auth')

@section('title', 'Cadastrar Exceção de CNPJ')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Cadastrar Nova Exceção de CNPJ</h2>
            <a href="{{ route('admin.cnpj-exceptions.index') }}" class="btn btn-primary">
                <i class="bi bi-arrow-left-circle me-2"></i>Voltar
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form class="needs-validation" action="{{ route('admin.cnpj-exceptions.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('cnpj_matriz') is-invalid @enderror"
                                    id="cnpj_matriz" name="cnpj_matriz" value="{{ old('cnpj_matriz') }}"
                                    placeholder="Digite o CNPJ completo da empresa matriz" required>
                                <label for="cnpj_matriz"><i class="bi bi-building me-2"></i>CNPJ da Matriz *</label>
                                @error('cnpj_matriz')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">O campo CNPJ da matriz é obrigatório.</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Digite o CNPJ da <strong>matriz</strong>. O sistema irá validar e buscar a Razão Social
                        correspondente na BrasilAPI para salvar a exceção.
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('admin.cnpj-exceptions.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Cadastrar Exceção
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
