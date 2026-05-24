@extends('layouts.auth')

@section('title', 'Gerenciar Estágios')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Gerenciar Estágios</h2>
            <a href="{{ route('admin.internships.index', array_merge(request()->except('show_deleted'), ['show_deleted' => $showDeleted ? 0 : 1])) }}"
                class="btn btn-outline-{{ $showDeleted ? 'secondary' : 'danger' }}">
                <i class="bi bi-trash{{ $showDeleted ? '' : '-fill' }} me-2"></i>
                {{ $showDeleted ? 'Ver Ativos' : 'Ver Deletados' }}
            </a>
        </div>

        {{-- Filtros de Pesquisa --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('admin.internships.index') }}">
                    <div class="row m-0 g-2 align-items-end">
                        <div class="col-md-4 col-lg-3">
                            <label for="search" class="form-label mb-0 small">Buscar por nome</label>
                            <input type="text" class="form-control form-control-sm" id="search" name="search"
                                value="{{ request('search') }}" placeholder="Nome do estudante">
                        </div>

                        <div class="col-md-4 col-lg-2">
                            <label for="registration" class="form-label mb-0 small">Matrícula</label>
                            <input type="text" class="form-control form-control-sm" id="registration" name="registration"
                                value="{{ request('registration') }}" placeholder="Nº da matrícula">
                        </div>

                        <div class="col-md-4 col-lg-2">
                            <label for="status" class="form-label mb-0 small">Status</label>
                            <select class="form-select form-select-sm" id="status" name="status">
                                <option value="">Todos</option>
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 col-lg-2">
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

                        <div class="col-md-6 col-lg-2">
                            <label for="end_date_from" class="form-label mb-0 small">Término de</label>
                            <input type="date" class="form-control form-control-sm" id="end_date_from"
                                name="end_date_from" value="{{ request('end_date_from') }}">
                        </div>

                        <div class="col-md-6 col-lg-2">
                            <label for="end_date_to" class="form-label mb-0 small">Término até</label>
                            <input type="date" class="form-control form-control-sm" id="end_date_to" name="end_date_to"
                                value="{{ request('end_date_to') }}">
                        </div>

                        <div class="col-md-4 col-lg-2">
                            <label for="order_by" class="form-label mb-0 small">Ordenar por</label>
                            <select class="form-select form-select-sm" id="order_by" name="order_by">
                                <option value="status_priority"
                                    {{ request('order_by', 'status_priority') == 'status_priority' ? 'selected' : '' }}>
                                    Status (Padrão)</option>
                                <option value="name_asc" {{ request('order_by') == 'name_asc' ? 'selected' : '' }}>Nome
                                    (A-Z)</option>
                                <option value="name_desc" {{ request('order_by') == 'name_desc' ? 'selected' : '' }}>Nome
                                    (Z-A)</option>
                                <option value="start_date_desc"
                                    {{ request('order_by') == 'start_date_desc' ? 'selected' : '' }}>Início (Mais recente)
                                </option>
                                <option value="start_date_asc"
                                    {{ request('order_by') == 'start_date_asc' ? 'selected' : '' }}>Início (Mais antigo)
                                </option>
                                <option value="end_date_desc"
                                    {{ request('order_by') == 'end_date_desc' ? 'selected' : '' }}>Término (Mais recente)
                                </option>
                                <option value="end_date_asc" {{ request('order_by') == 'end_date_asc' ? 'selected' : '' }}>
                                    Término (Mais antigo)</option>
                            </select>
                        </div>

                        <div class="col-md-12 col-lg-1">
                            <div class="d-flex gap-1">
                                <button type="submit" class="btn btn-outline-primary btn-sm flex-fill">
                                    <i class="bi bi-funnel"></i>
                                </button>
                                <a href="{{ route('admin.internships.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </a>
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
                            @if ($showDeleted)
                                <i class="bi bi-trash text-muted" style="font-size: 4rem;"></i>
                            @elseif (request()->hasAny(['search', 'registration', 'status', 'course_id', 'end_date_from', 'end_date_to']))
                                <i class="bi bi-search text-muted" style="font-size: 4rem;"></i>
                            @else
                                <i class="bi bi-briefcase text-muted" style="font-size: 4rem;"></i>
                            @endif
                        </div>
                        @if ($showDeleted)
                            <h4 class="text-muted mb-3">Nenhum estágio deletado</h4>
                            <p class="text-muted mb-4">
                                Não há estágios deletados no momento.<br>
                                Você pode alternar para ver os ativos.
                            </p>
                            <a href="{{ route('admin.internships.index', array_merge(request()->except('show_deleted'), ['show_deleted' => 0])) }}"
                                class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-2"></i>Ver Estágios Ativos
                            </a>
                        @elseif (request()->hasAny(['search', 'registration', 'status', 'course_id', 'end_date_from', 'end_date_to']))
                            <h4 class="text-muted mb-3">Nenhum estágio encontrado</h4>
                            <p class="text-muted mb-4">
                                Não foram encontrados estágios com os filtros aplicados.<br>
                                Tente ajustar os critérios de busca.
                            </p>
                            <a href="{{ route('admin.internships.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-2"></i>Ver Todos os Estágios
                            </a>
                        @else
                            <h4 class="text-muted mb-3">Nenhum estágio encontrado</h4>
                            <p class="text-muted mb-4">
                                Ainda não existem estágios cadastrados no sistema.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @else
            @foreach ($internships as $internship)
                <div class="card mb-3 shadow-sm border-0">
                    <div class="card-body py-3 px-4">
                        <div class="row m-0 align-items-center g-0">
                            <div class="col-md-9">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <h6 class="fw-bold text-dark mb-0">{{ $internship->student_name }}</h6>
                                    <span
                                        class="badge bg-{{ $internship->status->color() }}">{{ $internship->status->label() }}</span>
                                    <span class="badge bg-light text-dark">{{ $internship->course->name }}</span>
                                </div>
                                <div class="row m-0 small text-muted">
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <i class="bi bi-envelope me-1"></i>
                                            <strong>E-mail:</strong> {{ $internship->student_email }}
                                        </div>
                                        <div class="mb-1">
                                            <i class="bi bi-hash me-1"></i>
                                            <strong>Matrícula:</strong> {{ $internship->student_registration_number }}
                                        </div>
                                        <div class="mb-1">
                                            <i class="bi bi-calendar me-1"></i>
                                            <strong>Período:</strong>
                                            {{ $internship->start_date?->format('d/m/Y') ?? 'N/D' }} -
                                            {{ $internship->end_date?->format('d/m/Y') ?? 'N/D' }}
                                        </div>
                                        @if ($internship->status->value === 'Concluído' && $internship->evaluation_grade)
                                            <div class="mb-1">
                                                <i class="bi bi-award-fill me-1 text-warning"></i>
                                                <strong>Nota:</strong>
                                                {{ number_format($internship->evaluation_grade, 2, ',', '.') }}@if ($internship->internship_type_weight)
                                                    /{{ number_format($internship->internship_type_weight, 2, ',', '.') }}
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <i class="bi bi-building me-1"></i>
                                            <strong>Empresa:</strong> {{ $internship->company_name }}
                                        </div>
                                        <div class="mb-1">
                                            <i class="bi bi-person-check me-1"></i>
                                            <strong>Orientador:</strong> {{ $internship->advisor->name ?? 'N/D' }}
                                        </div>
                                        <div class="mb-1">
                                            <i class="bi bi-tag me-1"></i>
                                            <strong>Tipo:</strong>
                                            {{ $internship->internship_type_name ?? 'N/D' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 text-end">
                                @if ($showDeleted)
                                    <form action="{{ route('admin.internships.restore', $internship->id) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-sm px-3 py-1">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Restaurar
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.internships.edit', $internship->id) }}"
                                        class="btn btn-secondary btn-sm px-3 py-1">
                                        <i class="bi bi-pencil me-1"></i>Editar
                                    </a>
                                @endif
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
