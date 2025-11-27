@extends('layouts.auth')

@section('title', 'Estágios')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">
                @if (auth()->user()->can('is-coordenador'))
                    Estágios do Curso
                @else
                    Meus Estágios
                @endif
            </h2>
        </div>

        {{-- Filtros de Pesquisa --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('internship-view.index') }}">
                    <div class="row g-2 align-items-end">
                        {{-- Busca por nome do estudante --}}
                        <div class="col-md-3">
                            <label for="search" class="form-label mb-0 small">Nome do Estudante</label>
                            <input type="text" class="form-control form-control-sm" id="search" name="search"
                                value="{{ request('search') }}" placeholder="Buscar por nome">
                        </div>

                        {{-- Busca por matrícula --}}
                        <div class="col-md-{{ auth()->user()->can('is-coordenador') ? '2' : '5' }}">
                            <label for="registration" class="form-label mb-0 small">Matrícula</label>
                            <input type="text" class="form-control form-control-sm" id="registration" name="registration"
                                value="{{ request('registration') }}" placeholder="Matrícula">
                        </div>

                        {{-- Filtro por status --}}
                        <div class="col-md-2">
                            <label for="status" class="form-label mb-0 small">Status</label>
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

                        {{-- Filtro por orientador (apenas para coordenadores) --}}
                        @if (auth()->user()->can('is-coordenador'))
                            <div class="col-md-3">
                                <label for="advisor" class="form-label mb-0 small">Orientador</label>
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

                        {{-- Filtro de data de término --}}
                        <div class="col-md-{{ auth()->user()->can('is-coordenador') ? '2' : '3' }}">
                            <label for="end_date_from" class="form-label mb-0 small">Término de</label>
                            <input type="date" class="form-control form-control-sm" id="end_date_from" name="end_date_from"
                                value="{{ request('end_date_from') }}">
                        </div>

                        <div class="col-md-{{ auth()->user()->can('is-coordenador') ? '2' : '3' }}">
                            <label for="end_date_to" class="form-label mb-0 small">Término até</label>
                            <input type="date" class="form-control form-control-sm" id="end_date_to" name="end_date_to"
                                value="{{ request('end_date_to') }}">
                        </div>

                        {{-- Botões --}}
                        <div class="col-md-{{ auth()->user()->can('is-coordenador') ? '1' : '2' }}">
                            <div class="d-flex gap-1">
                                <button type="submit" class="btn btn-outline-primary btn-sm flex-fill">
                                    <i class="bi bi-funnel"></i> Filtrar
                                </button>
                                @if (request()->hasAny(['search', 'status', 'advisor', 'registration', 'end_date_from', 'end_date_to']))
                                    <a href="{{ route('internship-view.index') }}" class="btn btn-outline-secondary btn-sm"
                                        title="Limpar">
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
                            @if (request()->hasAny(['search', 'status', 'advisor', 'registration', 'end_date_from', 'end_date_to']))
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
                <div class="card mb-3 shadow-sm border-0">
                    <div class="card-body py-3 px-4">
                        <div class="row align-items-center g-0">
                            <div class="col-md-9">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <h6 class="fw-bold text-dark mb-0">{{ $internship->student_name }}</h6>
                                    <span class="badge bg-{{ $internship->status->color() }}">
                                        {{ $internship->status->label() }}
                                    </span>
                                    <span class="badge bg-light text-dark">
                                        {{ $internship->course->name }}
                                    </span>
                                    @if (auth()->user()->can('is-coordenador'))
                                        <span class="badge bg-secondary text-white">
                                            <i class="bi bi-person-badge me-1"></i>{{ $internship->advisor->name }}
                                        </span>
                                    @endif
                                </div>

                                <div class="row small text-muted">
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <i class="bi bi-envelope me-1"></i>
                                            {{ $internship->student_email }}
                                        </div>
                                        <div class="mb-1">
                                            <i class="bi bi-calendar-range me-1"></i>
                                            {{ $internship->start_date?->format('d/m/Y') ?? 'N/D' }} -
                                            {{ $internship->end_date?->format('d/m/Y') ?? 'N/D' }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <i class="bi bi-building me-1"></i>
                                            {{ $internship->company_name }}
                                        </div>
                                        <div class="mb-1">
                                            <i class="bi bi-person-check me-1"></i>
                                            {{ $internship->supervisor_name ?? 'N/D' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 text-end">
                                <a href="{{ route('internship-view.show', $internship->id) }}"
                                    class="btn btn-primary btn-sm px-3 py-1">
                                    <i class="bi bi-eye me-1"></i>Visualizar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="mt-3">
                {{ $internships->withQueryString()->links() }}
            </div>
        @endif

    </div>
@endsection
