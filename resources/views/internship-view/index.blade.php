@extends('layouts.auth')

@section('title', 'Estágios')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Estágios</h2>
        </div>

        @if ($internships->isEmpty())
            <div class="card">
                <div class="card-body">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-briefcase text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="text-muted mb-3">Nenhum estágio encontrado</h4>
                        <p class="text-muted mb-4">
                            Não há estágios atribuídos a você no momento.
                        </p>
                    </div>
                </div>
            </div>
        @else
            @foreach ($internships as $internship)
                <div class="card mb-3 shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <!-- Identificação do Estudante -->
                                <h5 class="card-title fw-bold text-dark mb-2">{{ $internship->student_name }}</h5>
                                <div class="d-flex gap-2 mb-3">
                                    <span
                                        class="badge bg-{{ $internship->status->color() }}">{{ $internship->status->label() }}</span>
                                    <span class="badge bg-light text-dark">{{ $internship->course->name }}</span>
                                </div>

                                <div class="row">
                                    <!-- Coluna 1: Dados do Estudante -->
                                    <div class="col-md-6">
                                        <p class="card-text mb-1">
                                            <i class="bi bi-envelope me-1"></i>
                                            <strong>E-mail:</strong> {{ $internship->student_email }}
                                        </p>
                                        <p class="card-text mb-1">
                                            <i class="bi bi-calendar me-1"></i>
                                            <strong>Período:</strong>
                                            {{ $internship->start_date?->format('d/m/Y') ?? 'Não definido' }} -
                                            {{ $internship->end_date?->format('d/m/Y') ?? 'Não definido' }}
                                        </p>
                                    </div>

                                    <!-- Coluna 2: Dados da Empresa e Supervisor -->
                                    <div class="col-md-6">
                                        <p class="card-text mb-1">
                                            <i class="bi bi-building me-1"></i>
                                            <strong>Concedente:</strong> {{ $internship->company_name }}
                                        </p>
                                        <p class="card-text mb-1">
                                            <i class="bi bi-person-check me-1"></i>
                                            <strong>Supervisor:</strong>
                                            {{ $internship->supervisor_name ?? 'Não definido' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Botão de Ação -->
                            <div class="ms-3">
                                <a href="{{ route('internship-view.show', $internship->id) }}"
                                    class="btn btn-outline-primary px-3 py-2">
                                    <i class="bi bi-eye me-1"></i>Visualizar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Links de Paginação -->
            <div class="mt-4">
                {{ $internships->links() }}
            </div>
        @endif

    </div>
@endsection
