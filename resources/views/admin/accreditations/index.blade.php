@extends('layouts.auth')

@section('title', 'Gerenciar Credenciamentos')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Gerenciar Credenciamentos</h2>
            <a href="{{ route('admin.accreditations.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Novo Credenciamento
            </a>
        </div>

        <!-- Filtros de Pesquisa -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('admin.accreditations.index') }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-9">
                            <input type="text" class="form-control form-control-sm" id="search" name="search"
                                value="{{ request('search') }}" placeholder="Buscar por nome, CPF ou número do processo">
                        </div>

                        <div class="col-md-3">
                            <div class="d-flex gap-1">
                                <button type="submit" class="btn btn-outline-primary btn-sm flex-fill">
                                    <i class="bi bi-funnel"></i> Filtrar
                                </button>
                                <a href="{{ route('admin.accreditations.index') }}"
                                    class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-arrow-clockwise"></i> Limpar
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if ($accreditations->isEmpty())
            <div class="card">
                <div class="card-body">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            @if (request('search'))
                                <i class="bi bi-search text-muted" style="font-size: 4rem;"></i>
                            @else
                                <i class="bi bi-award text-muted" style="font-size: 4rem;"></i>
                            @endif
                        </div>
                        @if (request('search'))
                            <h4 class="text-muted mb-3">Nenhum credenciamento encontrado</h4>
                            <p class="text-muted mb-4">
                                Não foram encontrados credenciamentos com os termos de busca aplicados.<br>
                                Tente ajustar os critérios de pesquisa.
                            </p>
                            <a href="{{ route('admin.accreditations.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-2"></i>Ver Todos os Credenciamentos
                            </a>
                        @else
                            <h4 class="text-muted mb-3">Nenhum credenciamento encontrado</h4>
                            <p class="text-muted mb-4">
                                Ainda não existem credenciamentos cadastrados.<br>
                                Comece adicionando o primeiro credenciamento.
                            </p>
                            <a href="{{ route('admin.accreditations.create') }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-plus-circle me-2"></i>Cadastrar Primeiro Credenciamento
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @else
            @foreach ($accreditations as $accreditation)
                <div class="card mb-2 shadow-sm border-0">
                    <div class="card-body py-2 px-3">
                        <div class="row align-items-center g-0">
                            <div class="col-md-4 fw-bold text-dark">{{ $accreditation->name }}</div>
                            <div class="col-md-3 small"><strong>CPF: </strong>{{ $accreditation->cpf }}</div>
                            <div class="col-md-3 small"><strong>Nº Processo: </strong>{{ $accreditation->process_number }}</div>
                            <div class="col-md-2 text-end">
                                <a href="{{ route('admin.accreditations.edit', $accreditation->id) }}"
                                    class="btn btn-secondary btn-sm px-3 py-1">
                                    <i class="bi bi-pencil me-1"></i>Editar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            {{ $accreditations->links() }} <!-- Paginação -->
        @endif
    </div>

    @push('scripts')
        <script>
            // Submit com Enter na busca
            document.getElementById('search').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.form.submit();
                }
            });
        </script>
    @endpush
@endsection
