@extends('layouts.auth')

@section('title', 'Avaliações do Supervisor')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Avaliações do Supervisor</h2>
            <a href="{{ route('admin.supervisor-evaluations.index', array_merge(request()->except('show_deleted'), ['show_deleted' => request('show_deleted') == '1' ? 0 : 1])) }}"
                class="btn btn-outline-{{ request('show_deleted') == '1' ? 'secondary' : 'danger' }}">
                <i class="bi bi-trash{{ request('show_deleted') == '1' ? '' : '-fill' }} me-2"></i>
                {{ request('show_deleted') == '1' ? 'Ver Ativas' : 'Ver Deletadas' }}
            </a>
        </div>

        {{-- Filtros de Pesquisa --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('admin.supervisor-evaluations.index') }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-6 col-lg-6">
                            <label for="search" class="form-label mb-0 small">Buscar por nome</label>
                            <input type="text" class="form-control form-control-sm" id="search" name="search"
                                value="{{ request('search') }}" placeholder="Nome do estagiário">
                        </div>

                        <div class="col-md-3 col-lg-3">
                            <label for="workload" class="form-label mb-0 small">Carga Horária</label>
                            <select class="form-select form-select-sm" id="workload" name="workload">
                                <option value="">Todos</option>
                                <option value="completed" {{ request('workload') == 'completed' ? 'selected' : '' }}>
                                    Cumprida</option>
                                <option value="not_completed"
                                    {{ request('workload') == 'not_completed' ? 'selected' : '' }}>Não Cumprida</option>
                            </select>
                        </div>

                        <div class="col-md-3 col-lg-3">
                            <div class="d-flex gap-1">
                                <button type="submit" class="btn btn-outline-primary btn-sm flex-fill">
                                    <i class="bi bi-funnel"></i> Filtrar
                                </button>
                                <a href="{{ route('admin.supervisor-evaluations.index') }}"
                                    class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if ($evaluations->isEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            @if (request('show_deleted') == '1')
                                <i class="bi bi-trash text-muted" style="font-size: 4rem;"></i>
                            @elseif (request()->hasAny(['search', 'workload']))
                                <i class="bi bi-search text-muted" style="font-size: 4rem;"></i>
                            @else
                                <i class="bi bi-clipboard-check text-muted" style="font-size: 4rem;"></i>
                            @endif
                        </div>
                        @if (request('show_deleted') == '1')
                            <h4 class="text-muted mb-3">Nenhuma avaliação deletada</h4>
                            <p class="text-muted mb-4">
                                Não há avaliações deletadas no momento.<br>
                                Você pode alternar para ver as ativas.
                            </p>
                            <a href="{{ route('admin.supervisor-evaluations.index', array_merge(request()->except('show_deleted'), ['show_deleted' => 0])) }}"
                                class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-2"></i>Ver Avaliações Ativas
                            </a>
                        @elseif (request()->hasAny(['search', 'workload']))
                            <h4 class="text-muted mb-3">Nenhuma avaliação encontrada</h4>
                            <p class="text-muted mb-4">
                                Não foram encontradas avaliações com os filtros aplicados.<br>
                                Tente ajustar os critérios de busca.
                            </p>
                            <a href="{{ route('admin.supervisor-evaluations.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-2"></i>Ver Todas as Avaliações
                            </a>
                        @else
                            <h4 class="text-muted mb-3">Nenhuma avaliação pendente</h4>
                            <p class="text-muted mb-4">
                                Não há avaliações de supervisor pendentes de associação.<br>
                                As avaliações aparecerão aqui após a sincronização.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @else
            @foreach ($evaluations as $evaluation)
                <div class="card mb-3 shadow-sm border-0">
                    <div class="card-body py-3 px-4">
                        <div class="row align-items-center g-0">
                            <div class="col-md-9">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <h6 class="fw-bold text-dark mb-0">
                                        {{ $evaluation->student_name ?? 'Nome não informado' }}</h6>
                                    @if ($evaluation->hasCompletedWorkload())
                                        <span class="badge bg-success">Carga Horária Cumprida</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Carga Horária Não Cumprida</span>
                                    @endif
                                </div>
                                <div class="row small text-muted">
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <i class="bi bi-person-badge me-1"></i>
                                            <strong>Supervisor:</strong> {{ $evaluation->supervisor_name ?? 'N/D' }}
                                        </div>
                                        <div class="mb-1">
                                            <i class="bi bi-envelope me-1"></i>
                                            <strong>E-mail:</strong> {{ $evaluation->supervisor_email ?? 'N/D' }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <i class="bi bi-briefcase me-1"></i>
                                            <strong>Cargo:</strong> {{ $evaluation->job_role ?? 'N/D' }}
                                        </div>
                                        <div class="mb-1">
                                            <i class="bi bi-calendar-plus me-1"></i>
                                            <strong>Recebida em:</strong> {{ $evaluation->created_at->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 text-end">
                                @if (request('show_deleted') == '1')
                                    <form action="{{ route('admin.supervisor-evaluations.restore', $evaluation->id) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-sm px-3 py-1">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Restaurar
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.supervisor-evaluations.edit', $evaluation) }}"
                                        class="btn btn-secondary btn-sm px-3 py-1">
                                        <i class="bi bi-pencil me-1"></i>Gerenciar
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection
