@extends('layouts.auth')

@section('title', 'Editar Tipo de Estágio')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Editar Tipo de Estágio</h2>
            <a href="{{ route('admin.internship-types.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left-circle me-2"></i>Voltar
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form class="needs-validation" action="{{ route('admin.internship-types.update', $internshipType->id) }}"
                    method="POST" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="row">
                        {{-- Nome do Tipo de Estágio --}}
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', $internshipType->name) }}"
                                    placeholder="Ex: Obrigatório" required>
                                <label for="name"><i class="bi bi-briefcase me-2"></i>Nome do Tipo de Estágio *</label>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">O campo nome do tipo de estágio é obrigatório.</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Carga Horária --}}
                        <div class="col-md-2 mb-3">
                            <div class="form-floating">
                                <input type="number" min="1" max="9999"
                                    class="form-control @error('required_hours') is-invalid @enderror" id="required_hours"
                                    name="required_hours"
                                    value="{{ old('required_hours', $internshipType->required_hours) }}"
                                    placeholder="Ex: 400" required>
                                <label for="required_hours"><i class="bi bi-clock me-2"></i>Carga horária *</label>
                                @error('required_hours')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Informe a carga horária.</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Peso --}}
                        <div class="col-md-2 mb-3">
                            <div class="form-floating">
                                <input type="number" min="1" max="10"
                                    class="form-control @error('weight') is-invalid @enderror" id="weight" name="weight"
                                    value="{{ old('weight', $internshipType->weight) }}" placeholder="Ex: 1" required>
                                <label for="weight"><i class="bi bi-percent me-2"></i>Peso *</label>
                                @error('weight')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Informe o peso.</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Curso --}}
                        <div class="col-md-2 mb-3">
                            <div class="form-floating">
                                <select class="form-select @error('course_id') is-invalid @enderror" id="course_id"
                                    name="course_id" required>
                                    <option value="">Selecione o curso</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}"
                                            {{ old('course_id', $internshipType->course_id) == $course->id ? 'selected' : '' }}>
                                            {{ $course->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="course_id"><i class="bi bi-book me-2"></i>Curso *</label>
                                @error('course_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Selecione o curso.</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Informações adicionais --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body py-2">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar-plus me-1"></i>
                                        <strong>Criado em:</strong> {{ $internshipType->created_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body py-2">
                                    <small class="text-muted">
                                        <i class="bi bi-pencil-square me-1"></i>
                                        <strong>Atualizado em:</strong>
                                        {{ $internshipType->updated_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <x-form-info-alert>
                        <li>A carga horária deve ser definida em horas</li>
                        <li>Alterações podem afetar estágios já cadastrados</li>
                    </x-form-info-alert>

                    {{-- Botões --}}
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.internship-types.index') }}" class="btn btn-secondary">
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
                        <i class="bi bi-trash me-2"></i>Excluir Tipo de Estágio
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
                    Tem certeza que deseja excluir este tipo de estágio? Esta ação pode ser desfeita.
                </div>
                <div class="modal-footer">
                    <form action="{{ route('admin.internship-types.destroy', $internshipType->id) }}" method="POST"
                        class="d-inline">
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
