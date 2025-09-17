@extends('layouts.auth')

@section('title', 'Detalhes do Estágio - ' . $internship->student_name)

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-1">Detalhes do Estágio</h2>
                <p class="text-muted mb-0">{{ $internship->student_name }} - {{ $internship->course->name }}</p>
            </div>
            <a href="{{ route('internship-view.index') }}" class="btn btn-primary">
                <i class="bi bi-arrow-left-circle me-2"></i>Voltar
            </a>
        </div>

        <!-- Status e Nota -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-3">
                            <h3 class="h5 mb-0">{{ $internship->student_name }}</h3>
                            <span class="badge bg-{{ $internship->status->color() }} fs-6">
                                {{ $internship->status->label() }}
                            </span>
                            <span class="badge bg-light text-dark fs-6">{{ $internship->course->name }}</span>
                        </div>
                    </div>
                    @if ($internship->evaluation_grade)
                        <div class="col-md-4">
                            <div class="d-flex align-items-center justify-content-md-end">
                                <i class="bi bi-star-fill text-warning me-2"></i>
                                <span class="h5 mb-0">{{ number_format($internship->evaluation_grade, 1) }}</span>
                                <small class="text-muted ms-1">/10</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Dados do Estudante -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-primary text-white py-2">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person me-2"></i>Dados do Estudante
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
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
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">Telefone</small>
                            <span class="fw-medium">{{ $internship->student_phone ?? 'Não informado' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dados da Empresa/Concedente -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-info text-white py-2">
                <h5 class="card-title mb-0">
                    <i class="bi bi-building me-2"></i>Parte Concedente
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-4 col-md-6">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">Empresa/Organização</small>
                            <span class="fw-medium">{{ $internship->company_name }}</span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">Telefone</small>
                            <span class="fw-medium">{{ $internship->company_phone ?? 'Não informado' }}</span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-3">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">E-mail</small>
                            <span class="fw-medium">{{ $internship->company_email ?? 'Não informado' }}</span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">Representante</small>
                            <span
                                class="fw-medium">{{ $internship->company_representative_name ?? 'Não informado' }}</span>
                        </div>
                    </div>
                    @if ($internship->field_of_activity)
                        <div class="col-lg-6 col-md-6">
                            <div class="d-flex flex-column">
                                <small class="text-muted mb-1">Área de Atividade</small>
                                <span class="fw-medium">{{ $internship->field_of_activity }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Dados do Supervisor -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-success text-white py-2">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person-check me-2"></i>Supervisor de Campo
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-4 col-md-6">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">Nome</small>
                            <span class="fw-medium">{{ $internship->supervisor_name ?? 'Não definido' }}</span>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-3">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">E-mail</small>
                            <span class="fw-medium">{{ $internship->supervisor_email ?? 'Não informado' }}</span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">Telefone</small>
                            <span class="fw-medium">{{ $internship->supervisor_phone ?? 'Não informado' }}</span>
                        </div>
                    </div>
                    @if ($internship->supervisor_role)
                        <div class="col-lg-2 col-md-6">
                            <div class="d-flex flex-column">
                                <small class="text-muted mb-1">Cargo</small>
                                <span class="fw-medium">{{ $internship->supervisor_role }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Dados do Estágio -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-warning py-2">
                <h5 class="card-title mb-0 text-dark">
                    <i class="bi bi-briefcase me-2"></i>Informações do Estágio
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">Tipo de Estágio</small>
                            <span class="fw-medium">{{ $internship->internship_type_name ?? 'Não definido' }}</span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">Setor</small>
                            <span class="fw-medium">{{ $internship->internship_sector ?? 'Não informado' }}</span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">Orientador</small>
                            <span class="fw-medium">{{ $internship->advisor->name ?? 'Não definido' }}</span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">Carga Horária</small>
                            <span
                                class="fw-medium">{{ $internship->required_hours ? $internship->required_hours . 'h' : 'Não definido' }}</span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">Data de Início</small>
                            <span
                                class="fw-medium">{{ $internship->start_date?->format('d/m/Y') ?? 'Não definido' }}</span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex flex-column">
                            <small class="text-muted mb-1">Data de Término</small>
                            <span class="fw-medium">{{ $internship->end_date?->format('d/m/Y') ?? 'Não definido' }}</span>
                        </div>
                    </div>
                    @if ($internship->start_date && $internship->end_date)
                        <div class="col-lg-3 col-md-6">
                            <div class="d-flex flex-column">
                                <small class="text-muted mb-1">Duração</small>
                                <span class="fw-medium">{{ $internship->start_date->diffInDays($internship->end_date) }}
                                    dias</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Atividades -->

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-secondary text-white py-2">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-task me-2"></i>Atividades Previstas
                </h5>
            </div>
            <div class="card-body">
                <div class="p-2 bg-light rounded">
                    {!! nl2br(e($internship->activities)) !!}
                </div>
            </div>
        </div>

    </div>
@endsection
