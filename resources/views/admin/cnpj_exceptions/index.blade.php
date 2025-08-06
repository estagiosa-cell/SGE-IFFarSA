@extends('layouts.auth')

@section('title', 'Gerenciar Exceções de CNPJ')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Gerenciar Exceções de CNPJ</h2>
            <a href="{{ route('admin.cnpj-exceptions.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Nova Exceção
            </a>
        </div>

        @if ($exceptions->isEmpty())
            <div class="card">
                <div class="card-body">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-exclamation-octagon text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="text-muted mb-3">Nenhuma exceção encontrada</h4>
                        <p class="text-muted mb-4">
                            Cadastre a primeira exceção para aplicar regras especiais na importação de estágios.
                        </p>
                        <a href="{{ route('admin.cnpj-exceptions.create') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-plus-circle me-2"></i>Cadastrar Exceção
                        </a>
                    </div>
                </div>
            </div>
        @else
            @foreach ($exceptions as $exception)
                <div class="card mb-3 shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title fw-bold text-dark mb-2">{{ $exception->razao_social }}</h5>
                                <div class="mb-2">
                                    <span><strong>CNPJ da Matriz:</strong>
                                        {{ \App\Utils\Formatter::formatCnpj($exception->cnpj_matriz) }}</span>
                                </div>
                            </div>
                            <a href="{{ route('admin.cnpj-exceptions.edit', $exception->id) }}"
                                class="btn btn-secondary px-3 py-2">
                                <i class="bi bi-pencil me-1"></i>Editar
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
            {{ $exceptions->links() }}
        @endif
    </div>
@endsection
