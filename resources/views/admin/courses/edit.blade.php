@extends('layouts.auth')

@section('title', 'Editar Curso')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Editar Curso - {{ $course->name }} </h2>
            <a href="{{ route('admin.courses.index') }}" class="btn btn-primary">
                <i class="bi bi-arrow-left-circle me-2"></i>Voltar
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form class="needs-validation" action="{{ route('admin.courses.update', $course->id) }}" method="POST"
                    novalidate>
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- Nome do Curso -->
                        <div class="col-md-6 mb-3">
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

                        <!-- Nível do Curso -->
                        <div class="col-md-3 mb-3">
                            <div class="form-floating">
                                <select class="form-select @error('level') is-invalid @enderror" id="level"
                                    name="level" required>
                                    <option value="">Selecione o nível</option>
                                    @foreach ($levels as $level)
                                        <option value="{{ $level->value }}"
                                            {{ old('level', $course->level->value) == $level->value ? 'selected' : '' }}>
                                            {{ $level->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="level"><i class="bi bi-layers me-2"></i>Nível *</label>
                                @error('level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Selecione o nível do curso.</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Tipo do Curso -->
                        <div class="col-md-3 mb-3">
                            <div class="form-floating">
                                <select class="form-select @error('type') is-invalid @enderror" id="type"
                                    name="type" required>
                                    <option value="">Selecione o tipo</option>
                                    @foreach ($types as $type)
                                        <option value="{{ $type->value }}"
                                            {{ old('type', $course->type->value) == $type->value ? 'selected' : '' }}>
                                            {{ $type->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="type"><i class="bi bi-tag me-2"></i>Tipo *</label>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Selecione o tipo do curso.</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Coordenador -->
                        <div class="col-md-6 mb-3">
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

                    <!-- Informações adicionais -->
                    <div class="row mb-3">
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
                        <li><strong>Compatibilidade por nível:</strong></li>
                        <li style="margin-left: 20px;"><strong>Ensino Médio:</strong> Técnico, FIC</li>
                        <li style="margin-left: 20px;"><strong>Ensino Superior:</strong> Bacharelado, Licenciatura,
                            Tecnologia, Sequencial</li>
                        <li style="margin-left: 20px;"><strong>Pós-graduação:</strong> Especialização, Mestrado, Doutorado
                        </li>
                    </x-form-info-alert>

                    <!-- Botões -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
