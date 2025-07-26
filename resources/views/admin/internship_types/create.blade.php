@extends('layouts.auth')

@section('title', 'Criar Tipo de Estágio')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Cadastrar Tipo de Estágio</h2>
            <a href="{{ route('admin.internship-types.index') }}" class="btn btn-primary">
                <i class="bi bi-arrow-left-circle me-2"></i>Voltar
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form class="needs-validation" action="{{ route('admin.internship-types.store') }}" method="POST" novalidate>
                    @csrf

                    <div class="row">
                        <!-- Nome do Tipo de Estágio -->
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}" placeholder="Ex: Obrigatório"
                                    required>
                                <label for="name"><i class="bi bi-briefcase me-2"></i>Nome do Tipo de Estágio *</label>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">O campo nome do tipo de estágio é obrigatório.</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Horas Obrigatórias -->
                        <div class="col-md-3 mb-3">
                            <div class="form-floating">
                                <input type="number" min="1" max="9999"
                                    class="form-control @error('required_hours') is-invalid @enderror" id="required_hours"
                                    name="required_hours" value="{{ old('required_hours') }}" placeholder="Ex: 400"
                                    required>
                                <label for="required_hours"><i class="bi bi-clock me-2"></i>Horas Obrigatórias *</label>
                                @error('required_hours')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Informe as horas obrigatórias.</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Curso -->
                        <div class="col-md-3 mb-3">
                            <div class="form-floating">
                                <select class="form-select @error('course_id') is-invalid @enderror" id="course_id"
                                    name="course_id" required>
                                    <option value="">Selecione o curso</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}"
                                            {{ old('course_id') == $course->id ? 'selected' : '' }}>
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

                    <!-- Botões -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.internship-types.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Cadastrar Tipo de Estágio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
