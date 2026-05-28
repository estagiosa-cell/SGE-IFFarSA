@extends('layouts.auth')

@section('title', 'Gerenciar Tipos de Estágio')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Gerenciar Tipos de Estágio</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.internship-types.index', array_merge(request()->except('show_deleted'), ['show_deleted' => $showDeleted ? 0 : 1])) }}"
                    class="btn btn-outline-{{ $showDeleted ? 'secondary' : 'danger' }}">
                    <i class="bi bi-trash{{ $showDeleted ? '' : '-fill' }} me-2"></i>
                    {{ $showDeleted ? 'Ver Ativos' : 'Ver Deletados' }}
                </a>
                <a href="{{ route('admin.internship-types.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Novo Tipo de Estágio
                </a>
            </div>
        </div>

        {{-- Filtros de Pesquisa --}}
        <div class="d-md-none mb-2">
            <button class="btn btn-outline-primary btn-lg w-100 d-flex align-items-center justify-content-center gap-2"
                type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse" aria-expanded="false"
                aria-controls="filtersCollapse">
                <i class="bi bi-funnel"></i>
                <span>Filtrar Tipos de Estágio</span>
                @if ($activeFiltersCount > 0)
                    <span class="badge text-bg-primary">{{ $activeFiltersCount }}</span>
                @endif
            </button>
        </div>

        <div class="collapse d-md-block" id="filtersCollapse">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('admin.internship-types.index') }}">
                        <div class="row m-0 g-2 align-items-end">
                            <div class="col-md-6 col-lg-6">
                                <label for="search" class="form-label mb-0 small">Buscar por nome</label>
                                <input type="text" class="form-control form-control-sm" id="search" name="search"
                                    value="{{ request('search') }}" placeholder="Nome do tipo de estágio">
                            </div>

                            <div class="col-md-3 col-lg-3">
                                <label for="course_id" class="form-label mb-0 small">Curso</label>
                                <select class="form-select form-select-sm" id="course_id" name="course_id">
                                    <option value="">Todos os Cursos</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}"
                                            {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 col-lg-3">
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-outline-primary btn-sm flex-fill">
                                        <i class="bi bi-funnel"></i> Filtrar
                                    </button>
                                    <a href="{{ route('admin.internship-types.index') }}"
                                        class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if ($internshipTypes->isEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            @if ($showDeleted)
                                <i class="bi bi-trash text-muted display-3"></i>
                            @elseif (request()->hasAny(['search', 'course_id']))
                                <i class="bi bi-search text-muted display-3"></i>
                            @else
                                <i class="bi bi-tags text-muted display-3"></i>
                            @endif
                        </div>
                        @if ($showDeleted)
                            <h4 class="text-muted mb-3">Nenhum tipo de estágio deletado</h4>
                            <p class="text-muted mb-4">
                                Não há tipos de estágio deletados no momento.<br>
                                Você pode alternar para ver os ativos.
                            </p>
                            <a href="{{ route('admin.internship-types.index', array_merge(request()->except('show_deleted'), ['show_deleted' => 0])) }}"
                                class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-2"></i>Ver Tipos de Estágio Ativos
                            </a>
                        @elseif (request()->hasAny(['search', 'course_id']))
                            <h4 class="text-muted mb-3">Nenhum tipo de estágio encontrado</h4>
                            <p class="text-muted mb-4">
                                Não foram encontrados tipos de estágio com os filtros aplicados.<br>
                                Tente ajustar os critérios de busca.
                            </p>
                            <a href="{{ route('admin.internship-types.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-2"></i>Ver Todos os Tipos de Estágio
                            </a>
                        @else
                            <h4 class="text-muted mb-3">Nenhum tipo de estágio encontrado</h4>
                            <p class="text-muted mb-4">
                                Ainda não existem tipos de estágio cadastrados.<br>
                                Comece adicionando o primeiro tipo de estágio.
                            </p>
                            <a href="{{ route('admin.internship-types.create') }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-plus-circle me-2"></i>Cadastrar Tipo de Estágio
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @else
            @foreach ($internshipTypes as $type)
                <div class="card mb-3 shadow-sm border-0">
                    <div class="card-body py-3 px-4">
                        <div class="row m-0 align-items-center g-0">
                            <div class="col-md-3 fw-bold text-dark">{{ $type->name }}</div>
                            <div class="col-md-2 small text-muted">
                                <strong>Carga horária:</strong> {{ $type->required_hours }}h
                            </div>
                            <div class="col-md-2 small text-muted">
                                <strong>Peso:</strong> {{ $type->weight }}
                            </div>
                            <div class="col-md-3 small text-muted">
                                <strong>Curso:</strong> {{ $type->course->name ?? 'Não informado' }}
                            </div>
                            <div class="col-md-2 text-end">
                                @if ($showDeleted)
                                    <form action="{{ route('admin.internship-types.restore', $type->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-sm px-3 py-1">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Restaurar
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.internship-types.edit', $type->id) }}"
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
                {{ $internshipTypes->withQueryString()->links() }}
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            // Auto-submit do formulário quando o select de curso mudar
            document.getElementById('course_id').addEventListener('change', function() {
                if (this.value !== '') {
                    this.form.submit();
                }
            });

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
