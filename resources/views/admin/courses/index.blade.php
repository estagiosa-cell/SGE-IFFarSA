@extends('layouts.auth')

@section('title', 'Gerenciar Cursos')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Gerenciar Cursos</h2>
            <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Novo Curso
            </a>
        </div>

        <!-- Filtros de Pesquisa -->
        <div class="card mb-3">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('admin.courses.index') }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <input type="text" class="form-control form-control-sm" id="search" name="search"
                                value="{{ request('search') }}" placeholder="Buscar por nome do curso">
                        </div>

                        <div class="col-md-2">
                            <select class="form-select form-select-sm" id="level" name="level">
                                <option value="">Nível</option>
                                @foreach ($levels as $level)
                                    <option value="{{ $level->value }}"
                                        {{ request('level') == $level->value ? 'selected' : '' }}>
                                        {{ $level->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <select class="form-select form-select-sm" id="type" name="type">
                                <option value="">Tipo</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->value }}"
                                        {{ request('type') == $type->value ? 'selected' : '' }}>
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <div class="d-flex gap-1">
                                <button type="submit" class="btn btn-outline-primary btn-sm flex-fill">
                                    <i class="bi bi-funnel"></i> Filtrar
                                </button>
                                <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-arrow-clockwise"></i> Limpar Filtros
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if ($courses->isEmpty())
            <div class="card">
                <div class="card-body">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            @if (request()->hasAny(['search', 'level', 'type']))
                                <i class="bi bi-search text-muted" style="font-size: 4rem;"></i>
                            @else
                                <i class="bi bi-book text-muted" style="font-size: 4rem;"></i>
                            @endif
                        </div>
                        @if (request()->hasAny(['search', 'level', 'type']))
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
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title fw-bold text-dark mb-2">{{ $course->name }}</h5>
                                <div class="d-flex gap-2 mb-2">
                                    <span class="badge bg-primary">{{ $course->level->label() }}</span>
                                    <span class="badge bg-secondary">{{ $course->type->label() }}</span>
                                </div>
                                <p class="card-text mb-0">
                                    <i class="bi bi-person-check me-1"></i>
                                    <strong>Coordenador:</strong>
                                    <span>{{ $course->coordinator->name ?? 'Não há coordenador cadastrado' }}</span>
                                </p>
                            </div>
                            <a href="" class="btn btn-secondary px-3 py-2">
                                <i class="bi bi-pencil me-1"></i>Editar
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach

            {{ $courses->links() }} <!-- Paginação -->
    </div>
    @endif
    </div>

    @push('scripts')
        <script>
            // Auto-submit do formulário quando os selects mudarem
            document.getElementById('level').addEventListener('change', function() {
                if (this.value !== '') {
                    this.form.submit();
                }
            });

            document.getElementById('type').addEventListener('change', function() {
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
