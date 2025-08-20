@extends('layouts.auth')

@section('title', 'Editar Estágio')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Editar Estágio - {{ $internship->student_name }}</h2>
            <a href="{{ route('admin.internships.index') }}" class="btn btn-primary">
                <i class="bi bi-arrow-left-circle me-2"></i>Voltar
            </a>
        </div>

        <form action="{{ route('admin.internships.update', $internship->id) }}" method="POST" class="needs-validation"
            novalidate>
            @csrf
            @method('PUT')



            <!-- Informações do Sistema -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light text-dark">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informações do Sistema</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body py-2">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar-plus me-1"></i>
                                        <strong>Criado em:</strong> {{ $internship->created_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body py-2">
                                    <small class="text-muted">
                                        <i class="bi bi-pencil-square me-1"></i>
                                        <strong>Atualizado em:</strong> {{ $internship->updated_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        @if ($internship->google_docs_id)
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body py-2">
                                        <small class="text-muted">
                                            <i class="bi bi-file-text me-1"></i>
                                            <strong>Google Docs ID:</strong> {{ $internship->google_docs_id }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.internships.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Salvar Alterações
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
