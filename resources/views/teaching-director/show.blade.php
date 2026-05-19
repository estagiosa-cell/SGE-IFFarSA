@extends('layouts.auth')

@section('title', 'Detalhes do Estágio - ' . $internship->student_name)

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-1">Detalhes do Estágio</h2>
                <p class="text-muted mb-0">{{ $internship->student_name }} - {{ $internship->course->name }}</p>
            </div>
            <a href="{{ route('teaching-director.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left-circle me-2"></i>Voltar
            </a>
        </div>

        {{-- Status do Estágio --}}
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body py-3">
                <div class="row m-0 align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-3">
                            <h3 class="h5 mb-0">{{ $internship->student_name }}</h3>
                            <span class="badge bg-{{ $internship->status->color() }} fs-6">
                                {{ $internship->status->label() }}
                            </span>
                            <span class="badge bg-light text-dark fs-6">{{ $internship->course->name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dados do Estudante --}}
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-primary text-white py-2">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person me-2"></i>Dados do Estudante
                </h5>
            </div>
            <div class="card-body">
                <div class="row m-0 g-3">
                    <div class="col-lg-4 col-md-6">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">Nome Completo</small>
                            <span class="fw-medium">{{ $internship->student_name }}</span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">Matrícula</small>
                            <span class="fw-medium">{{ $internship->student_registration_number }}</span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-3">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">Curso</small>
                            <span class="fw-medium">{{ $internship->course->name }}</span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">E-mail</small>
                            <span class="fw-medium">{{ $internship->student_email }}</span>
                        </div>
                    </div>
                    @if ($internship->student_year_semester)
                        <div class="col-lg-2 col-md-3">
                            <div class="d-flex flex-column">
                                <small class="text-muted mb-1">Ano/Semestre</small>
                                <span class="fw-medium">{{ $internship->student_year_semester }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Dados do Estágio --}}
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-primary text-white py-2">
                <h5 class="card-title mb-0">
                    <i class="bi bi-briefcase me-2"></i>Dados do Estágio
                </h5>
            </div>
            <div class="card-body">
                <div class="row m-0 g-3">
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">Status</small>
                            <span class="badge bg-{{ $internship->status->color() }} w-auto align-self-start">
                                {{ $internship->status->label() }}
                            </span>
                        </div>
                    </div>
                    @if ($internship->internship_type_name)
                        <div class="col-lg-3 col-md-6">
                            <div class="d-flex flex-column">
                                <small class="text-muted mb-1">Tipo de Estágio</small>
                                <span class="fw-medium">{{ $internship->internship_type_name }}</span>
                            </div>
                        </div>
                    @endif
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">Data de Início</small>
                            <span class="fw-medium">{{ $internship->start_date?->format('d/m/Y') ?? 'Não definido' }}</span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">Data de Término</small>
                            <span class="fw-medium">{{ $internship->end_date?->format('d/m/Y') ?? 'Não definido' }}</span>
                        </div>
                    </div>
                    @if ($internship->required_hours)
                        <div class="col-lg-3 col-md-6">
                            <div class="d-flex flex-column">
                                <small class="text-muted mb-1">Carga Horária Exigida</small>
                                <span class="fw-medium">{{ $internship->required_hours }} horas</span>
                            </div>
                        </div>
                    @endif
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">Orientador</small>
                            <span class="fw-medium">{{ $internship->advisor->name ?? 'Não definido' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Local do Estágio --}}
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-primary text-white py-2">
                <h5 class="card-title mb-0">
                    <i class="bi bi-building me-2"></i>Local do Estágio
                </h5>
            </div>
            <div class="card-body">
                <div class="row m-0 g-3">
                    <div class="col-lg-6 col-md-6">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">Empresa</small>
                            <span class="fw-medium">{{ $internship->company_name ?? 'Não informado' }}</span>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">Cidade</small>
                            <span class="fw-medium">{{ $internship->company_address_city ?? 'Não informado' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
