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

        <!-- Filtros de Pesquisa -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('admin.courses.index') }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-9">
                            <label for="search" class="form-label mb-0 small">Buscar</label>
                            <input type="text" class="form-control form-control-sm" id="search" name="search"
                                value="{{ request('search') }}" placeholder="Nome do curso">
                        </div>

                        <div class="col-md-3">
                            <div class="d-flex gap-1">
                                <button type="submit" class="btn btn-outline-primary btn-sm flex-fill">
                                    <i class="bi bi-funnel"></i> Filtrar
                                </button>
                                <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-arrow-clockwise"></i> Limpar
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if ($courses->isEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            @if ($showDeleted)
                                <i class="bi bi-trash text-muted" style="font-size: 4rem;"></i>
                            @elseif (request()->filled('search'))
                                <i class="bi bi-search text-muted" style="font-size: 4rem;"></i>
                            @else
                                <i class="bi bi-book text-muted" style="font-size: 4rem;"></i>
                            @endif
                        </div>
                        @if ($showDeleted)
                            <h4 class="text-muted mb-3">Nenhum curso deletado</h4>
                            <p class="text-muted mb-4">
                                Não há cursos deletados no momento.<br>
                                Você pode alternar para ver os ativos.
                            </p>
                            <a href="{{ route('admin.courses.index', array_merge(request()->except('show_deleted'), ['show_deleted' => 0])) }}"
                                class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-2"></i>Ver Cursos Ativos
                            </a>
                        @elseif (request()->filled('search'))
                            <h4 class="text-muted mb-3">Nenhum curso encontrado</h4>
                            <p class="text-muted mb-4">
                                Não foram encontrados cursos com os filtros aplicados.<br>
                                Tente ajustar os critérios de busca.
                            </p>
                            <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-2"></i>Ver Todos os Cursos
                            </a>
                        @else
                            <h4 class="text-muted mb-3">Nenhum curso encontrado</h4>
                            <p class="text-muted mb-4">
                                Ainda não existem cursos cadastrados.<br>
                                Comece adicionando o primeiro curso da instituição.
                            </p>
                            <a href="{{ route('admin.courses.create') }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-plus-circle me-2"></i>Cadastrar Primeiro Curso
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @else
            @foreach ($courses as $course)
                <div class="card mb-3 shadow-sm border-0">
                    <div class="card-body py-3 px-4">
                        <div class="row align-items-center g-0">
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
        @endif
    </div>
@endsection
