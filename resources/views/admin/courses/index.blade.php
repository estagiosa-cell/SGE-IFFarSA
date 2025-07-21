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

        <div class="card">
            <div class="card-body">
                @if ($courses->isEmpty())
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
                @else

                @endif
            </div>
        </div>
    </div>
@endsection
