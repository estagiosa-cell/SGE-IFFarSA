@extends('layouts.auth')

@section('title', 'Estágios')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Meus Estágios</h2>
        </div>

        <!-- Filtros de Pesquisa -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('internship-view.index') }}">
                    <div class="row g-2 align-items-end">
                        <!-- Busca por nome do estudante -->
                        <div class="col-md-3">
                            <label for="search" class="form-label small text-muted mb-1">Nome do Estudante</label>
                            <input type="text" class="form-control form-control-sm" id="search" name="search"
                                value="{{ request('search') }}" placeholder="Buscar por nome...">
                        </div>

                        <!-- Busca por matrícula -->
                        <div class="col-md-{{ auth()->user()->can('is-coordenador') ? '2' : '5' }}">
                            <label for="registration" class="form-label small text-muted mb-1">Matrícula</label>
                            <input type="text" class="form-control form-control-sm" id="registration" name="registration"
                                value="{{ request('registration') }}" placeholder="Matrícula do estudante...">
                        </div>

                        <!-- Filtro por status -->
                        <div class="col-md-2">
                            <label for="status" class="form-label small text-muted mb-1">Status</label>
                            <select class="form-select form-select-sm" id="status" name="status">
                                <option value="">Todos</option>
                                @foreach ($statusOptions as $statusKey => $statusLabel)
                                    <option value="{{ $statusKey }}"
                                        {{ request('status') == $statusKey ? 'selected' : '' }}>
                                        {{ $statusLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filtro por orientador (apenas para coordenadores) -->
                        @if (auth()->user()->can('is-coordenador'))
                            <div class="col-md-3">
                                <label for="advisor" class="form-label small text-muted mb-1">Orientador</label>
                                <select class="form-select form-select-sm" id="advisor" name="advisor">
                                    <option value="">Todos</option>
                                    @foreach ($advisors as $advisor)
                                        <option value="{{ $advisor->id }}"
                                            {{ request('advisor') == $advisor->id ? 'selected' : '' }}>
                                            {{ $advisor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Botões -->
                        <div class="col-md-2">
                            <div class="d-flex gap-1">
                                <button type="submit" class="btn btn-outline-primary btn-sm flex-fill">
                                    <i class="bi bi-funnel"></i> Filtrar
                                </button>
                                @if (request()->hasAny(['search', 'status', 'advisor', 'registration']))
                                    <a href="{{ route('internship-view.index') }}" class="btn btn-outline-secondary btn-sm"
                                        title="Limpar Filtros">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if ($internships->isEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            @if (request()->hasAny(['search', 'status', 'advisor', 'registration']))
                                <i class="bi bi-search text-muted" style="font-size: 4rem;"></i>
                            @else
                                <i class="bi bi-briefcase text-muted" style="font-size: 4rem;"></i>
                            @endif
                        </div>
                        <h4 class="text-muted mb-3">Nenhum estágio encontrado</h4>
                        <p class="text-muted mb-4">
                            @if (request()->hasAny(['search', 'status', 'advisor', 'registration']))
                                Não encontramos estágios com os filtros aplicados.
                                <br>
                                <a href="{{ route('internship-view.index') }}" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Limpar filtros
                                </a>
                            @else
                                Não há estágios atribuídos a você no momento.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @else
            @foreach ($internships as $internship)
                <div class="card mb-3 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-lg-9">
                                <!-- Identificação do Estudante -->
                                <div class="d-flex align-items-center mb-2">
                                    <h5 class="card-title mb-0 me-3 fw-bold">{{ $internship->student_name }}</h5>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <span class="badge bg-{{ $internship->status->color() }} rounded-pill">
                                            {{ $internship->status->label() }}
                                        </span>
                                        <span class="badge bg-light text-dark border rounded-pill">
                                            {{ $internship->course->name }}
                                        </span>
                                        @if (auth()->user()->can('is-coordenador'))
                                            <span
                                                class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill">
                                                <i class="bi bi-person-badge me-1"></i>{{ $internship->advisor->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="row g-2 mt-2">
                                    <!-- Coluna 1: Dados do Estudante -->
                                    <div class="col-md-6">
                                        <p class="card-text mb-2 small text-muted">
                                            <i class="bi bi-envelope me-1"></i>
                                            {{ $internship->student_email }}
                                        </p>
                                        <p class="card-text mb-0 small text-muted">
                                            <i class="bi bi-calendar-range me-1"></i>
                                            {{ $internship->start_date?->format('d/m/Y') ?? 'Não definido' }} -
                                            {{ $internship->end_date?->format('d/m/Y') ?? 'Não definido' }}
                                        </p>
                                    </div>

                                    <!-- Coluna 2: Dados da Empresa e Supervisor -->
                                    <div class="col-md-6">
                                        <p class="card-text mb-2 small text-muted">
                                            <i class="bi bi-building me-1"></i>
                                            {{ $internship->company_name }}
                                        </p>
                                        <p class="card-text mb-0 small text-muted">
                                            <i class="bi bi-person-check me-1"></i>
                                            {{ $internship->supervisor_name ?? 'Não definido' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Botão de Ação -->
                            <div class="col-lg-3 text-lg-end mt-3 mt-lg-0">
                                <a href="{{ route('internship-view.show', $internship->id) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>Visualizar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Links de Paginação -->
            <div class="mt-4">
                {{ $internships->links() }}
            </div>
        @endif

    </div>
@endsection
