@extends('layouts.auth')

@section('title', 'Gerenciar Partes Concedentes')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Gerenciar Partes Concedentes</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.companies.index', array_merge(request()->except('show_deleted'), ['show_deleted' => $showDeleted ? 0 : 1])) }}"
                    class="btn btn-outline-{{ $showDeleted ? 'secondary' : 'danger' }}">
                    <i class="bi bi-trash{{ $showDeleted ? '' : '-fill' }} me-2"></i>
                    {{ $showDeleted ? 'Ver Ativos' : 'Ver Deletados' }}
                </a>
                <a href="{{ route('admin.companies.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Nova Parte Concedente
                </a>
            </div>
        </div>

        {{-- Filtros de Pesquisa --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('admin.companies.index') }}">
                    <div class="row m-0 g-2 align-items-end">
                        <div class="col-md-4 col-lg-4">
                            <label for="name" class="form-label mb-0 small">Nome / Razão Social</label>
                            <input type="text" class="form-control form-control-sm" id="name" name="name"
                                value="{{ $searchName }}" placeholder="Nome ou parte do nome">
                        </div>

                        <div class="col-md-3 col-lg-3">
                            <label for="legal_identifier" class="form-label mb-0 small">CPF / CNPJ</label>
                            <input type="text" class="form-control form-control-sm" id="legal_identifier"
                                name="legal_identifier" value="{{ $searchLegalIdentifier }}"
                                placeholder="Número do documento">
                        </div>

                        <div class="col-md-3 col-lg-3">
                            <label for="address_city" class="form-label mb-0 small">Cidade</label>
                            <select class="form-select form-select-sm" id="address_city" name="address_city">
                                <option value="">Todas as cidades</option>
                                @foreach ($cities as $city)
                                    <option value="{{ $city }}" {{ $searchCity == $city ? 'selected' : '' }}>
                                        {{ $city }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 col-lg-2">
                            <div class="d-flex gap-1">
                                <button type="submit" class="btn btn-outline-primary btn-sm flex-fill">
                                    <i class="bi bi-funnel"></i> Filtrar
                                </button>
                                <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if ($companies->isEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            @if ($showDeleted)
                                <i class="bi bi-trash text-muted display-3"></i>
                            @elseif (request()->hasAny(['name', 'legal_identifier', 'address_city']))
                                <i class="bi bi-search text-muted display-3"></i>
                            @else
                                <i class="bi bi-building-gear text-muted display-3"></i>
                            @endif
                        </div>
                        @if ($showDeleted)
                            <h4 class="text-muted mb-3">Nenhuma parte concedente deletada</h4>
                            <p class="text-muted mb-4">
                                Não há partes concedentes deletadas no momento.<br>
                                Você pode alternar para ver as ativas.
                            </p>
                            <a href="{{ route('admin.companies.index', array_merge(request()->except('show_deleted'), ['show_deleted' => 0])) }}"
                                class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-2"></i>Ver Partes Concedentes Ativas
                            </a>
                        @elseif (request()->hasAny(['name', 'legal_identifier', 'address_city']))
                            <h4 class="text-muted mb-3">Nenhuma parte concedente encontrada</h4>
                            <p class="text-muted mb-4">
                                Não foram encontrados registros com os filtros aplicados.<br>
                                Tente ajustar os critérios de busca.
                            </p>
                            <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-2"></i>Ver Todos os Registros
                            </a>
                        @else
                            <h4 class="text-muted mb-3">Nenhuma parte concedente cadastrada</h4>
                            <p class="text-muted mb-4">
                                Ainda não existem partes concedentes na sua base de dados.<br>
                                Comece adicionando o primeiro registro.
                            </p>
                            <a href="{{ route('admin.companies.create') }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-plus-circle me-2"></i>Cadastrar Parte Concedente
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @else
            @foreach ($companies as $company)
                <div class="card mb-3 shadow-sm border-0">
                    <div class="card-body py-3 px-4">
                        <div class="row m-0 align-items-center g-0">
                            <div class="col-md-5 fw-bold text-dark">{{ $company->name }}</div>
                            <div class="col-md-4 small text-muted">

                                @php
                                    $doc = preg_replace('/\D/', '', $company->legal_identifier);
                                @endphp
                                @if (strlen($doc) === 11)
                                    <strong>CPF: </strong>{{ App\Utils\Formatter::formatCPF($doc) }}
                                @elseif(strlen($doc) === 14)
                                    <strong>CNPJ: </strong>{{ App\Utils\Formatter::formatCNPJ($doc) }}
                                @else
                                    <strong>CPF/CNPJ: </strong>{{ $company->legal_identifier }}
                                @endif
                            </div>
                            <div class="col-md-3 text-end">
                                @if ($showDeleted)
                                    <form action="{{ route('admin.companies.restore', $company->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-sm px-3 py-1">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Restaurar
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.companies.edit', $company->id) }}"
                                        class="btn btn-secondary btn-sm px-3 py-1">
                                        <i class="bi bi-pencil me-1"></i>Editar
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Paginação --}}
            <div class="mt-3">
                {{ $companies->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection
