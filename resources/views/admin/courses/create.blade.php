@extends('layouts.auth')

@section('title', 'Criar Curso')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Cadastrar Curso</h2>
            <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left-circle me-2"></i>Voltar
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form class="needs-validation" action="{{ route('admin.courses.store') }}" method="POST" novalidate>
                    @csrf

                    <div class="row m-0 m-0">
                        {{-- Nome do Curso --}}
                        <div class="col-md-8 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}"
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
                                            {{ old('coordinator_id') == $coordinator->id ? 'selected' : '' }}>
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

                    <x-form-info-alert>
                        <li>O nome do curso deve ser único no sistema</li>
                    </x-form-info-alert>

                    {{-- Botões --}}
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Cadastrar Curso
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection
