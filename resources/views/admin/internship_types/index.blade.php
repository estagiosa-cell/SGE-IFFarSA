@extends('layouts.auth')

@section('title', 'Gerenciar Tipos de Estágio')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Gerenciar Tipos de Estágio</h2>
            <a href="{{ route('admin.internship-types.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Novo Tipo de Estágio
            </a>
        </div>

        @if ($internshipTypes->isEmpty())
            <div class="card">
                <div class="card-body">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-briefcase text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="text-muted mb-3">Nenhum tipo de estágio encontrado</h4>
                        <p class="text-muted mb-4">
                            Ainda não existem tipos de estágio cadastrados.<br>
                            Comece adicionando o primeiro tipo de estágio.
                        </p>
                        <a href="{{ route('admin.internship-types.create') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-plus-circle me-2"></i>Cadastrar Tipo de Estágio
                        </a>
                    </div>
                </div>
            </div>
        @else
            @foreach ($internshipTypes as $type)
                <div class="card mb-3 shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title fw-bold text-dark mb-2">{{ $type->name }}</h5>
                                <div class="mb-2">
                                    <span><strong>Carga horária:</strong> {{ $type->required_hours }}h</span><br>
                                    <span><strong>Curso:</strong> {{ $type->course->name ?? 'Curso não informado' }}</span>
                                </div>
                            </div>
                            <a href="{{ route('admin.internship-types.edit', $type->id) }}"
                                class="btn btn-secondary px-3 py-2">
                                <i class="bi bi-pencil me-1"></i>Editar
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
            {{ $internshipTypes->links() }}
        @endif
    </div>
@endsection
