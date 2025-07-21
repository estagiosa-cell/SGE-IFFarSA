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

        @if ($courses->isEmpty())
            <div class="card">
                <div class="card-body">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-book text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="text-muted mb-3">Nenhum curso encontrado</h4>
                        <p class="text-muted mb-4">
                            Ainda não existem cursos cadastrados.<br>
                            Comece adicionando o primeiro curso da instituição.
                        </p>
                        <a href="{{ route('admin.courses.create') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-plus-circle me-2"></i>Cadastrar Primeiro Curso
                        </a>
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
                                    <span class="badge bg-primary">{{ $course->level }}</span>
                                    <span class="badge bg-secondary">{{ $course->type }}</span>
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
@endsection
