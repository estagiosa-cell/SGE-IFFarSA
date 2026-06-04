@extends('layouts.auth')

@section('title', 'Editar Curso')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Editar Curso - {{ $course->name }}</h2>
            <x-ui.back-button url="{{ route('admin.courses.index') }}" />
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form class="needs-validation" action="{{ route('admin.courses.update', $course->id) }}" method="POST"
                    novalidate>
                    @csrf
                    @method('PUT')

                    <div class="row m-0">
                        {{-- Nome do Curso --}}
                        <div class="col-md-6 mb-3">
                            <x-form.input name="name" value="{{ $course->name }}" icon="bi-book" label="Nome do Curso *" placeholder="Ex: Técnico em Informática" required feedback="O campo nome do curso é obrigatório." />
                        </div>

                        {{-- Coordenador --}}
                        <div class="col-md-3 mb-3">
                            <x-form.select name="coordinator_id" icon="bi-person-badge" label="Coordenador">
                                <option value="">Nenhum coordenador</option>
                                @foreach ($coordinators as $coordinator)
                                    <option value="{{ $coordinator->id }}"
                                        {{ old('coordinator_id', $course->coordinator_id) == $coordinator->id ? 'selected' : '' }}>
                                        {{ $coordinator->name }}
                                    </option>
                                @endforeach
                            </x-form.select>
                        </div>

                        {{-- Coordenador secundário --}}
                        <div class="col-md-3 mb-3">
                            <x-form.select name="secondary_coordinator_id" icon="bi-person-badge" label="Coordenador Secundário">
                                <option value="">Nenhum coordenador secundário</option>
                                @foreach ($coordinators as $coordinator)
                                    <option value="{{ $coordinator->id }}"
                                        {{ old('secondary_coordinator_id', $course->secondary_coordinator_id) == $coordinator->id ? 'selected' : '' }}>
                                        {{ $coordinator->name }}
                                    </option>
                                @endforeach
                            </x-form.select>
                        </div>
                    </div>

                    {{-- Informações adicionais --}}
                    <div class="row m-0 mb-3">
                        <div class="col-md-6">
                            <div class="card bg-light border-0">
                                <div class="card-body py-2">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar-plus me-1"></i>
                                        <strong>Criado em:</strong> {{ $course->created_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light border-0">
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
                    </x-form-info-alert>

                    {{-- Botões --}}
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </a>
                            {{-- Botão para acionar o modal de exclusão --}}
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                data-bs-target="#deleteModal">
                                <i class="bi bi-trash me-2"></i>Excluir
                            </button>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <x-modal.delete action="{{ route('admin.courses.destroy', $course->id) }}" />
@endsection
