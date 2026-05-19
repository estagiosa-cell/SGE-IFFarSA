@extends('layouts.auth')

@section('title', 'Editar Curso')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Editar Curso - {{ $course->name }}</h2>
            <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left-circle me-2"></i>Voltar
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form class="needs-validation" action="{{ route('admin.courses.update', $course->id) }}" method="POST"
                    novalidate>
                    @csrf
                    @method('PUT')

                    <div class="row m-0 m-0">
                        {{-- Nome do Curso --}}
                        <div class="col-md-8 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', $course->name) }}"
                                    placeholder="Ex: Técnico em Informática" required>
                                <label for="name"><i class="bi bi-book me-2"></i>Nome do Curso *</label>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">O campo nome do curso é obrigatório.</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Coordenador --}}
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <select class="form-select @error('coordinator_id') is-invalid @enderror"
                                    id="coordinator_id" name="coordinator_id">
                                    <option value="">Nenhum coordenador</option>
                                    @foreach ($coordinators as $coordinator)
                                        <option value="{{ $coordinator->id }}"
                                            {{ old('coordinator_id', $course->coordinator_id) == $coordinator->id ? 'selected' : '' }}>
                                            {{ $coordinator->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="coordinator_id"><i class="bi bi-person-badge me-2"></i>Coordenador</label>
                                @error('coordinator_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Informações adicionais --}}
                    <div class="row m-0 mb-3">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body py-2">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar-plus me-1"></i>
                                        <strong>Criado em:</strong> {{ $course->created_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body py-2">
                                    <small class="text-muted">
                                        <i class="bi bi-pencil-square me-1"></i>
                                        <strong>Atualizado em:</strong> {{ $course->updated_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <x-form-info-alert>
                        <li>O nome do curso deve ser único no sistema</li>
                        <li>Alterações podem afetar tipos de estágio relacionados</li>
                    </x-form-info-alert>

                    {{-- Botões --}}
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Salvar Alterações
                        </button>
                    </div>
                </form>
                <hr>
                <div class="d-flex gap-2 mt-3">
                    {{-- Botão Deletar --}}
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="bi bi-trash me-2"></i>Excluir Curso
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Excluir --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja excluir este curso? Esta ação pode ser desfeita.
                </div>
                <div class="modal-footer">
                    <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Excluir
                        </button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
@endsection
