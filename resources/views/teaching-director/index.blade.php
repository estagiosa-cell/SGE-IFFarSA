@extends('layouts.auth')

@section('title', 'Consulta de Estágios - Direção de Ensino')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Consulta de Estágios</h2>
        </div>

        {{-- Filtros de Pesquisa --}}
        <div class="d-md-none mb-2">
            <button class="btn btn-outline-primary btn-lg w-100 d-flex align-items-center justify-content-center gap-2"
                type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse" aria-expanded="false"
                aria-controls="filtersCollapse">
                <i class="bi bi-funnel"></i>
                <span>Filtrar Estágios</span>
                @if ($activeFiltersCount > 0)
                    <span class="badge text-bg-primary">{{ $activeFiltersCount }}</span>
                @endif
            </button>
        </div>

        <div class="collapse d-md-block" id="filtersCollapse">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('teaching-director.index') }}">
                        <div class="row m-0 g-2 align-items-end">
                            {{-- Busca por nome do estudante --}}
                            <div class="col-md-6 col-lg-2">
                                <label for="search" class="form-label mb-0 small">Nome do Estudante</label>
                                <input type="text" class="form-control form-control-sm" id="search" name="search"
                                    value="{{ request('search') }}" placeholder="Buscar por nome">
                            </div>

                            {{-- Busca por matrícula --}}
                            <div class="col-md-6 col-lg-2">
                                <label for="registration" class="form-label mb-0 small">Matrícula</label>
                                <input type="text" class="form-control form-control-sm" id="registration"
                                    name="registration" value="{{ request('registration') }}" placeholder="Matrícula">
                            </div>

                            {{-- Filtro por curso --}}
                            <div class="col-md-6 col-lg-2">
                                <label for="course_id" class="form-label mb-0 small">Curso</label>
                                <select class="form-select form-select-sm" id="course_id" name="course_id">
                                    <option value="">Todos</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}"
                                            {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filtro por orientador --}}
                            <div class="col-md-6 col-lg-2">
                                <label for="advisor_id" class="form-label mb-0 small">Orientador</label>
                                <select class="form-select form-select-sm" id="advisor_id" name="advisor_id">
                                    <option value="">Todos</option>
                                    @foreach ($advisors as $advisor)
                                        <option value="{{ $advisor->id }}"
                                            {{ request('advisor_id') == $advisor->id ? 'selected' : '' }}>
                                            {{ $advisor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filtro por status --}}
                            <div class="col-md-6 col-lg-2">
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

                            {{-- Filtro de data de término (de) --}}
                            <div class="col-md-6 col-lg-2">
                                <label for="end_date_from" class="form-label mb-0 small">Término de</label>
                                <input type="date" class="form-control form-control-sm" id="end_date_from"
                                    name="end_date_from" value="{{ request('end_date_from') }}">
                            </div>

                            {{-- Filtro de data de término (até) --}}
                            <div class="col-md-6 col-lg-2">
                                <label for="end_date_to" class="form-label mb-0 small">Término até</label>
                                <input type="date" class="form-control form-control-sm" id="end_date_to"
                                    name="end_date_to" value="{{ request('end_date_to') }}">
                            </div>

                            {{-- Filtro de ordenação --}}
                            <div class="col-md-6 col-lg-2">
                                <label for="order_by" class="form-label mb-0 small">Ordenar por</label>
                                <select class="form-select form-select-sm" id="order_by" name="order_by">
                                    <option value="status_priority"
                                        {{ request('order_by', 'status_priority') == 'status_priority' ? 'selected' : '' }}>
                                        Status (Padrão)</option>
                                    <option value="name_asc" {{ request('order_by') == 'name_asc' ? 'selected' : '' }}>Nome
                                        (A-Z)</option>
                                    <option value="name_desc" {{ request('order_by') == 'name_desc' ? 'selected' : '' }}>
                                        Nome (Z-A)</option>
                                    <option value="start_date_desc"
                                        {{ request('order_by') == 'start_date_desc' ? 'selected' : '' }}>Início (Mais
                                        recente)</option>
                                    <option value="start_date_asc"
                                        {{ request('order_by') == 'start_date_asc' ? 'selected' : '' }}>Início (Mais
                                        antigo)</option>
                                    <option value="end_date_desc"
                                        {{ request('order_by') == 'end_date_desc' ? 'selected' : '' }}>Término (Mais
                                        recente)</option>
                                    <option value="end_date_asc"
                                        {{ request('order_by') == 'end_date_asc' ? 'selected' : '' }}>Término (Mais antigo)
                                    </option>
                                </select>
                            </div>

                            {{-- Botões --}}
                            <div class="col-md-12 col-lg-2">
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-outline-primary btn-sm flex-fill">
                                        <i class="bi bi-funnel"></i> Filtrar
                                    </button>
                                    @if (request()->hasAny(['search', 'status', 'course_id', 'advisor_id', 'registration', 'end_date_from', 'end_date_to']))
                                        <a href="{{ route('teaching-director.index') }}"
                                            class="btn btn-outline-secondary btn-sm" title="Limpar">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if ($internships->isEmpty())
            <x-ui.empty-state 
                icon="{{ request()->hasAny(['search', 'status', 'course_id', 'advisor_id', 'registration', 'end_date_from', 'end_date_to']) ? 'bi-search' : 'bi-briefcase' }}"
                title="Nenhum estágio encontrado"
                description="{!! request()->hasAny(['search', 'status', 'course_id', 'advisor_id', 'registration', 'end_date_from', 'end_date_to']) ? 'Não encontramos estágios com os filtros aplicados.<br>Tente ajustar os critérios de busca.' : 'Não há estágios cadastrados no sistema.' !!}"
            >
                @if (request()->hasAny(['search', 'status', 'course_id', 'advisor_id', 'registration', 'end_date_from', 'end_date_to']))
                    <a href="{{ route('teaching-director.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>Ver Todos os Estágios
                    </a>
                @endif
            </x-ui.empty-state>
        @else
            <div class="text-muted small mb-3">
                Mostrando de <strong>{{ $internships->firstItem() }}</strong> a <strong>{{ $internships->lastItem() }}</strong> de <strong>{{ $internships->total() }}</strong> resultados
            </div>
            @foreach ($internships as $internship)
                <div class="card mb-3 shadow-sm border-0">
                    <div class="card-body py-3 px-4">
                        <div class="row m-0 align-items-center g-0">
                            <div class="col-md-9">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <h6 class="fw-bold text-dark mb-0">{{ $internship->student_name }}</h6>
                                    <span class="badge bg-{{ $internship->status->color() }}">
                                        {{ $internship->status->label() }}
                                    </span>
                                    <span class="badge bg-light text-dark">
                                        {{ $internship->course->name }}
                                    </span>
                                </div>

                                <div class="row m-0 small text-muted">
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <i class="bi bi-hash me-1"></i>
                                            <strong>Matrícula:</strong> {{ $internship->student_registration_number }}
                                        </div>
                                        <div class="mb-1">
                                            <i class="bi bi-envelope me-1"></i>
                                            <strong>E-mail:</strong> {{ $internship->student_email }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <i class="bi bi-calendar-range me-1"></i>
                                            <strong>Período:</strong>
                                            {{ $internship->start_date?->format('d/m/Y') ?? 'N/D' }} -
                                            {{ $internship->end_date?->format('d/m/Y') ?? 'N/D' }}
                                        </div>
                                        <div class="mb-1">
                                            <i class="bi bi-person-badge me-1"></i>
                                            <strong>Orientador:</strong> {{ $internship->advisor->name ?? 'N/D' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 text-end">
                                <a href="{{ route('teaching-director.show', $internship->id) }}"
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
