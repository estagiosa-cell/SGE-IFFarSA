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
            <a href="{{ route('internship-view.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left-circle me-2"></i>Voltar
            </a>
        </div>

        {{-- Status e Nota --}}
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

        {{-- Dados da Empresa/Concedente --}}
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-primary text-white py-2">
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

        {{-- Supervisor --}}
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-primary text-white py-2">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person-badge me-2"></i>Supervisor
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

        {{-- Dados do Estágio --}}
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-primary text-white py-2">
                <h5 class="card-title mb-0">
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
                            <small class="text-muted mb-1">Peso</small>
                            <span class="fw-medium">{{ $internship->internship_type_weight ?? 'Não definido' }}</span>
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

        {{-- Atividades --}}
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-primary text-white py-2">
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

        {{-- Avaliação do Supervisor --}}
        @if ($internship->evaluation_performance)
            @php
                // Resolve numeric value for a textual concept using internship stored values when present
                $getNumericValue = function ($value) use ($internship) {
                    $text = trim((string) ($value ?? ''));

                    $defaults = [
                        'Ótimo' => 2.0,
                        'Muito Bom' => 1.5,
                        'Bom' => 1.0,
                        'Satisfatório' => 0.5,
                        'Insatisfatório' => 0.0,
                    ];

                    $mapKey = $text;

                    $conceptFieldMap = [
                        'Ótimo' => 'great_value',
                        'Muito Bom' => 'very_good_value',
                        'Bom' => 'good_value',
                        'Satisfatório' => 'satisfactory_value',
                        'Insatisfatório' => 'unsatisfactory_value',
                    ];

                    // If internship has a stored numeric value for this concept, use it.
                    if (isset($conceptFieldMap[$mapKey]) && isset($internship->{$conceptFieldMap[$mapKey]})) {
                        return (float) $internship->{$conceptFieldMap[$mapKey]};
                    }

                    // Fallback to defaults
                    return $defaults[$mapKey] ?? 0.0;
                };
            @endphp
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-primary text-white py-2">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clipboard-check me-2"></i>Avaliação do Supervisor
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="fw-bold">Supervisor: </span>
                        <span>{{ $internship->evaluation_supervisor_name ?? ($internship->supervisor_name ?? 'Não informado') }}</span>
                    </div>

                    {{-- Informações Gerais da Avaliação --}}
                    <h6 class="text-primary mb-3">Informações Gerais</h6>
                    <div class="row g-3 mb-4">
                        @if ($internship->evaluation_completed_workload)
                            <div class="col-lg-3 col-md-6">
                                <div class="d-flex flex-column">
                                    <small class="text-muted mb-1">Carga Horária Cumprida</small>
                                    <span class="fw-medium">
                                        @if (strtolower($internship->evaluation_completed_workload) === 'sim')
                                            <span class="badge bg-success">Sim</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Não</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endif
                        @if ($internship->evaluation_training_course)
                            <div class="col-lg-3 col-md-6">
                                <div class="d-flex flex-column">
                                    <small class="text-muted mb-1">Curso de Formação do supervisor</small>
                                    <span class="fw-medium">{{ $internship->evaluation_training_course }}</span>
                                </div>
                            </div>
                        @endif
                        @if ($internship->evaluation_education_level)
                            <div class="col-lg-3 col-md-6">
                                <div class="d-flex flex-column">
                                    <small class="text-muted mb-1">Nível de Escolaridade do supervisor</small>
                                    <span class="fw-medium">{{ $internship->evaluation_education_level }}</span>
                                </div>
                            </div>
                        @endif
                        @if ($internship->evaluation_job_role)
                            <div class="col-lg-3 col-md-6">
                                <div class="d-flex flex-column">
                                    <small class="text-muted mb-1">Cargo/Função do supervisor</small>
                                    <span class="fw-medium">{{ $internship->evaluation_job_role }}</span>
                                </div>
                            </div>
                        @endif
                        @if ($internship->evaluation_experience_time)
                            <div class="col-lg-3 col-md-6">
                                <div class="d-flex flex-column">
                                    <small class="text-muted mb-1">Tempo de Experiência do supervisor</small>
                                    <span class="fw-medium">{{ $internship->evaluation_experience_time }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Critérios de Avaliação --}}
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-primary text-white py-2">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-list-check me-2"></i>Critérios de Avaliação
                    </h5>
                </div>
                <div class="card-body">
                    @can('is-coordenador')
                        @php
                            $criteria = [
                                'evaluation_performance' => '1. Desempenho',
                                'evaluation_comprehension' => '2. Compreensão',
                                'evaluation_technical_knowledge' => '3. Conhecimento Técnico',
                                'evaluation_organization' => '4. Organização',
                                'evaluation_initiative' => '5. Iniciativa',
                                'evaluation_attendance' => '6. Assiduidade',
                                'evaluation_discipline' => '7. Disciplina',
                                'evaluation_sociability' => '8. Sociabilidade',
                                'evaluation_cooperation' => '9. Cooperação',
                                'evaluation_responsibility' => '10. Responsabilidade',
                            ];
                        @endphp

                        @php
                            $chunks = array_chunk($criteria, 5, true);
                        @endphp

                        <div class="row g-3 mb-4">
                            @foreach ($chunks as $chunk)
                                <div class="col-md-6">
                                    <div class="row">
                                        @foreach ($chunk as $field => $label)
                                            @if ($internship->{$field})
                                                <div class="col-md-12 mb-3">
                                                    <div
                                                        class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                                        <span class="text-muted">{{ $label }}</span>
                                                        <span class="badge bg-primary">{{ $internship->{$field} }}
                                                            ({{ number_format($getNumericValue($internship->{$field}), 1) }})
                                                        </span>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endcan
                    {{-- Nota Final --}}
                    @if ($internship->evaluation_grade)
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <div class="alert alert-success mb-0" role="alert">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bi bi-star-fill me-2"></i>
                                            <strong>Nota Final da Avaliação</strong>
                                        </div>
                                        <span
                                            class="badge bg-success fs-5">{{ number_format($internship->evaluation_grade, 2) }}/{{ number_format($internship->internship_type_weight, 1) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    {{-- Observações --}}
                    @if (
                        $internship->evaluation_considerations ||
                            $internship->evaluation_suggestions_to_institution ||
                            $internship->evaluation_performance_issues ||
                            $internship->evaluation_other_observations)
                        <h6 class="text-primary mb-3">Observações</h6>
                        @if ($internship->evaluation_considerations)
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1">Considerações</small>
                                <div class="p-3 bg-light rounded">
                                    {!! nl2br(e($internship->evaluation_considerations)) !!}
                                </div>
                            </div>
                        @endif
                        @if ($internship->evaluation_performance_issues)
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1">Aspectos que Prejudicaram o Desempenho</small>
                                <div class="p-3 bg-light rounded">
                                    {!! nl2br(e($internship->evaluation_performance_issues)) !!}
                                </div>
                            </div>
                        @endif
                        @can('is-coordenador')
                            @if ($internship->evaluation_suggestions_to_institution)
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-1">Sugestões à Instituição</small>
                                    <div class="p-3 bg-light rounded">
                                        {!! nl2br(e($internship->evaluation_suggestions_to_institution)) !!}
                                    </div>
                                </div>
                            @endif
                        @endcan
                    @endif
                </div>
            </div>

    </div>
    @endif

    </div>
@endsection
