@extends('layouts.auth')

@section('title', 'Gerenciar Cursos')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Gerenciar Cursos</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.courses.index', array_merge(request()->except('show_deleted'), ['show_deleted' => $showDeleted ? 0 : 1])) }}"
                    class="btn btn-outline-{{ $showDeleted ? 'secondary' : 'danger' }}">
                    <i class="bi bi-trash{{ $showDeleted ? '' : '-fill' }} me-2"></i>
                    {{ $showDeleted ? 'Ver Ativos' : 'Ver Deletados' }}
                </a>
                <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Novo Curso
                </a>
            </div>
        </div>

        {{-- Filtros de Pesquisa --}}
        <div class="d-md-none mb-2">
            <button class="btn btn-outline-primary btn-lg w-100 d-flex align-items-center justify-content-center gap-2"
                type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse" aria-expanded="false"
                aria-controls="filtersCollapse">
                <i class="bi bi-funnel"></i>
                <span>Filtrar Cursos</span>
                @if ($activeFiltersCount > 0)
                    <span class="badge text-bg-primary">{{ $activeFiltersCount }}</span>
                @endif
            </button>
        </div>

        <div class="collapse d-md-block" id="filtersCollapse">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('admin.courses.index') }}">
                        <div class="row m-0 g-2 align-items-end">
                            <div class="col-md-9 col-lg-9">
                                <label for="search" class="form-label mb-0 small">Buscar por nome</label>
                                <input type="text" class="form-control form-control-sm" id="search" name="search"
                                    value="{{ request('search') }}" placeholder="Nome do curso">
                            </div>

                            <div class="col-md-3 col-lg-3">
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-outline-primary btn-sm flex-fill">
                                        <i class="bi bi-funnel"></i> Filtrar
                                    </button>
                                    <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if ($courses->isEmpty())
            <x-ui.empty-state 
                icon="{{ $showDeleted ? 'bi-trash' : (request()->filled('search') ? 'bi-search' : 'bi-book') }}"
                title="{{ $showDeleted ? 'Nenhum curso deletado' : (request()->filled('search') ? 'Nenhum curso encontrado' : 'Nenhum curso cadastrado') }}"
                description="{!! $showDeleted ? 'Não há cursos deletados no momento.<br>Você pode alternar para ver os ativos.' : (request()->filled('search') ? 'Não foram encontrados cursos com os filtros aplicados.<br>Tente ajustar os critérios de busca.' : 'Ainda não existem cursos cadastrados.<br>Comece adicionando o primeiro curso da instituição.') !!}"
            >
                @if ($showDeleted)
                    <a href="{{ route('admin.courses.index', array_merge(request()->except('show_deleted'), ['show_deleted' => 0])) }}"
                        class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>Ver Cursos Ativos
                    </a>
                @elseif (request()->filled('search'))
                    <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>Ver Todos os Cursos
                    </a>
                @else
                    <a href="{{ route('admin.courses.create') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-plus-circle me-2"></i>Cadastrar Primeiro Curso
                    </a>
                @endif
            </x-ui.empty-state>
        @else
            <div class="text-muted small mb-3">
                Mostrando de <strong>{{ $courses->firstItem() }}</strong> a <strong>{{ $courses->lastItem() }}</strong> de <strong>{{ $courses->total() }}</strong> resultados
            </div>
            @foreach ($courses as $course)
                <div class="card mb-3 shadow-sm border-0">
                    <div class="card-body py-3 px-4">
                        <div class="row m-0 align-items-center g-0">
                            <div class="col-md-7 fw-bold text-dark">{{ $course->name }}</div>
                            <div class="col-md-3 small text-muted">
                                <i class="bi bi-person-check me-1"></i>
                                <strong>Coordenador:</strong>
                                {{ $course->coordinator->name ?? 'Não cadastrado' }}
                            </div>
                            <div class="col-md-2 text-end">
                                @if ($showDeleted)
                                    <form action="{{ route('admin.courses.restore', $course->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-sm px-3 py-1">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Restaurar
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.courses.edit', $course->id) }}"
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
                {{ $courses->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection
