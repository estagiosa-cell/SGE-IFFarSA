@extends('layouts.auth')

@section('title', 'Dashboard')

@section('main-content')
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Dashboard</h2>
            <form method="POST" action="{{ route('admin.sync.data') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-arrow-repeat me-2"></i>Sincronizar Dados
                </button>
            </form>
        </div>

        {{-- Estatísticas Gerais --}}
        <div class="row m-0 mb-4">
            {{-- Total de Estágios --}}
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-start border-primary border-4 shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row m-0 no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                    Total de Estágios
                                </div>
                                <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalInternships }}</div>
                                <small class="text-muted">{{ $deletedInternships }} Excluído{{ $deletedInternships != 1 ? 's' : '' }}</small>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-briefcase fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total de Partes Concedentes --}}
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-start border-success border-4 shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row m-0 no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                    Partes Concedentes
                                </div>
                                <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalCompanies }}</div>
                                <small class="text-muted">{{ $deletedCompanies }} Excluída{{ $deletedCompanies != 1 ? 's' : '' }} | {{ $activeCompanies }} com estágio{{ $activeCompanies != 1 ? 's' : '' }} ativo{{ $activeCompanies != 1 ? 's' : '' }}</small>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-building fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total de Usuários --}}
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-start border-info border-4 shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row m-0 no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-info text-uppercase mb-1">
                                    Total de Usuários
                                </div>
                                <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalUsers }}</div>
                                <small class="text-muted">{{ $deletedUsers }} Excluído{{ $deletedUsers != 1 ? 's' : '' }}</small>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total de Cursos --}}
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-start border-warning border-4 shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row m-0 no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                    Total de Cursos
                                </div>
                                <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalCourses }}</div>
                                <small class="text-muted">{{ $deletedCourses }} Excluído{{ $deletedCourses != 1 ? 's' : '' }}</small>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-book fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total de Avaliações do Supervisor --}}
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-start border-secondary border-4 shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row m-0 no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-secondary text-uppercase mb-1">
                                    Avaliações do Supervisor
                                </div>
                                <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalEvaluations }}</div>
                                <small class="text-muted">{{ $deletedEvaluations }} Excluída{{ $deletedEvaluations != 1 ? 's' : '' }}</small>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-clipboard-check fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total de Estágios Aditivados --}}
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-start border-dark border-4 shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row m-0 no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-dark text-uppercase mb-1">
                                    Estágios Aditivados
                                </div>
                                <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalInternshipsWithAmendments }}</div>
                                <small class="text-muted">Total de {{ $totalActiveAmendments }} aditivo{{ $totalActiveAmendments != 1 ? 's' : '' }}</small>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-file-earmark-plus fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráficos e estatísticas detalhadas --}}
        <div class="row m-0 mb-4">
            {{-- Estágios por Status --}}
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-primary">Estágios por Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="row m-0">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Pendente</span>
                                    <span class="badge bg-warning text-dark">{{ $internshipsByStatus['pending'] }}</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-warning" role="progressbar"
                                        style="width: {{ $totalInternships > 0 ? ($internshipsByStatus['pending'] / $totalInternships) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Aguardando Assinatura</span>
                                    <span class="badge bg-info">{{ $internshipsByStatus['awaiting_signature'] }}</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-info" role="progressbar"
                                        style="width: {{ $totalInternships > 0 ? ($internshipsByStatus['awaiting_signature'] / $totalInternships) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Liberado</span>
                                    <span class="badge bg-secondary">{{ $internshipsByStatus['released'] }}</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-secondary" role="progressbar"
                                        style="width: {{ $totalInternships > 0 ? ($internshipsByStatus['released'] / $totalInternships) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Em Andamento</span>
                                    <span class="badge bg-primary">{{ $internshipsByStatus['in_progress'] }}</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-primary" role="progressbar"
                                        style="width: {{ $totalInternships > 0 ? ($internshipsByStatus['in_progress'] / $totalInternships) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Concluído</span>
                                    <span class="badge bg-success">{{ $internshipsByStatus['completed'] }}</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                        style="width: {{ $totalInternships > 0 ? ($internshipsByStatus['completed'] / $totalInternships) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Cancelado</span>
                                    <span class="badge bg-danger">{{ $internshipsByStatus['cancelled'] }}</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-danger" role="progressbar"
                                        style="width: {{ $totalInternships > 0 ? ($internshipsByStatus['cancelled'] / $totalInternships) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Usuários por Tipo --}}
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header bg-light py-2">
                        <h6 class="m-0 fw-bold text-primary">Usuários por Tipo</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $roleLabels = [
                                'admin' => 'Admin',
                                'coordenador' => 'Coordenador',
                                'orientador' => 'Orientador',
                            ];
                            $roleColors = [
                                'admin' => 'danger',
                                'coordenador' => 'primary',
                                'orientador' => 'success',
                            ];
                        @endphp
                        @foreach ($roleLabels as $roleValue => $roleLabel)
                            @php
                                $count = $usersByRole[$roleValue] ?? 0;
                            @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">{{ $roleLabel }}</span>
                                    <span class="badge bg-{{ $roleColors[$roleValue] }}">{{ $count }}</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-{{ $roleColors[$roleValue] }}" role="progressbar"
                                        style="width: {{ $totalUsers > 0 ? ($count / $totalUsers) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Estágios Recentes --}}
        <div class="row m-0">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header bg-light py-2">
                        <h6 class="m-0 fw-bold text-primary">Estágios Recentes</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            {{-- Cabeçalho Desktop --}}
                            <div class="list-group-item bg-light d-none d-lg-block text-muted fw-bold">
                                <div class="row m-0">
                                    <div class="col-lg-2">Aluno</div>
                                    <div class="col-lg-2">Empresa</div>
                                    <div class="col-lg-2">Curso</div>
                                    <div class="col-lg-2">Orientador</div>
                                    <div class="col-lg-2">Status</div>
                                    <div class="col-lg-2">Data de Criação</div>
                                </div>
                            </div>

                            @forelse($recentInternships as $internship)
                                <a href="{{ route('admin.internships.edit', $internship) }}"
                                    class="list-group-item list-group-item-action py-3">
                                    <div class="row m-0 align-items-center">
                                        <div class="col-12 col-lg-2 mb-2 mb-lg-0">
                                            <span class="d-lg-none fw-bold text-muted small text-uppercase">Aluno: </span>
                                            <span class="d-block text-truncate"
                                                title="{{ $internship->student_name }}">{{ $internship->student_name }}</span>
                                        </div>
                                        <div class="col-12 col-lg-2 mb-2 mb-lg-0">
                                            <span class="d-lg-none fw-bold text-muted small text-uppercase">Empresa:
                                            </span>
                                            <span class="d-block text-truncate"
                                                title="{{ $internship->company_name }}">{{ $internship->company_name }}</span>
                                        </div>
                                        <div class="col-12 col-lg-2 mb-2 mb-lg-0">
                                            <span class="d-lg-none fw-bold text-muted small text-uppercase">Curso: </span>
                                            <span class="d-block text-truncate"
                                                title="{{ $internship->course->name ?? 'N/A' }}">{{ $internship->course->name ?? 'N/A' }}</span>
                                        </div>
                                        <div class="col-12 col-lg-2 mb-2 mb-lg-0">
                                            <span class="d-lg-none fw-bold text-muted small text-uppercase">Orientador:
                                            </span>
                                            <span class="d-block text-truncate"
                                                title="{{ $internship->advisor->name ?? 'N/A' }}">{{ $internship->advisor->name ?? 'N/A' }}</span>
                                        </div>
                                        <div class="col-12 col-lg-2 mb-2 mb-lg-0">
                                            <span class="d-lg-none fw-bold text-muted small text-uppercase me-1">Status:
                                            </span>
                                            @php
                                                $color = $internship->status
                                                    ? $internship->status->color()
                                                    : 'secondary';
                                                $label = $internship->status ? $internship->status->label() : 'N/A';
                                            @endphp
                                            <span class="badge bg-{{ $color }}">{{ $label }}</span>
                                        </div>
                                        <div class="col-12 col-lg-2 text-muted small">
                                            <span class="d-lg-none fw-bold text-muted small text-uppercase me-1">Data:
                                            </span>
                                            {{ $internship->created_at->format('d/m/Y H:i') }}
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="list-group-item text-center text-muted py-4">
                                    Nenhum estágio cadastrado ainda
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sincronização Feedback --}}
    @if(session('sync_result'))
        @php
            $sync = session('sync_result');
            $totalProcessed = ($sync['internships']['processed'] ?? 0) + ($sync['evaluations']['processed'] ?? 0);
            $totalErrors = count($sync['internships']['errors'] ?? []) + count($sync['evaluations']['errors'] ?? []);
        @endphp

        @if($totalProcessed === 0 && $totalErrors === 0 && empty($sync['form']))
            {{-- Toast --}}
            <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
                <div id="syncToast" class="toast align-items-center text-white bg-info border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            Nenhum dado novo para sincronizar.
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var toastEl = document.getElementById('syncToast');
                    var toast = new bootstrap.Toast(toastEl);
                    toast.show();
                });
            </script>
        @else
            {{-- Modal --}}
            @php
                $headerClass = 'bg-success text-white'; // No errors
                if ($totalErrors > 0 && $totalProcessed > 0) {
                    $headerClass = 'bg-warning text-dark'; // Mixed
                } elseif ($totalErrors > 0 && $totalProcessed === 0) {
                    $headerClass = 'bg-danger text-white'; // All failed
                }
            @endphp
            <div class="modal fade" id="syncModal" tabindex="-1" aria-labelledby="syncModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header {{ $headerClass }}">
                            <h5 class="modal-title" id="syncModalLabel">Resultado da Sincronização</h5>
                            <button type="button" class="btn-close {{ $headerClass === 'bg-warning text-dark' ? '' : 'btn-close-white' }}" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            
                            {{-- Estágios --}}
                            <h6>Estágios sincronizados</h6>
                            @php $processedInternships = $sync['internships']['processed'] ?? 0; @endphp
                            @if($processedInternships === 0)
                                <p>Nenhum novo registro importado.</p>
                            @elseif($processedInternships === 1)
                                <p>1 registro importado com sucesso.</p>
                            @else
                                <p>{{ $processedInternships }} registros importados com sucesso.</p>
                            @endif
                            @if(count($sync['internships']['errors'] ?? []) > 0)
                                 <div class="list-group mb-4">
                                     <div class="list-group-item bg-light text-uppercase fw-bold text-secondary py-2" style="font-size: 0.75rem;">
                                         <div class="row g-2">
                                             <div class="col-2 col-sm-1">Linha</div>
                                             <div class="col-4 col-sm-4">Estagiário</div>
                                             <div class="col-6 col-sm-7">Motivo do Erro</div>
                                         </div>
                                     </div>
                                     @foreach($sync['internships']['errors'] as $err)
                                         <div class="list-group-item py-2">
                                             <div class="row g-2 align-items-center">
                                                 <div class="col-2 col-sm-1">
                                                     <span class="badge bg-secondary rounded-pill">#{{ $err['line'] ?? '-' }}</span>
                                                 </div>
                                                 <div class="col-4 col-sm-4 fw-semibold text-dark text-truncate" title="{{ $err['student'] ?? '-' }}">
                                                     {{ $err['student'] ?? '-' }}
                                                 </div>
                                                 <div class="col-6 col-sm-7">
                                                     <div class="bg-danger-subtle text-danger border border-danger-subtle rounded px-2 py-1 text-wrap">
                                                         <i class="bi bi-exclamation-triangle me-1"></i> {{ $err['reason'] ?? '-' }}
                                                     </div>
                                                 </div>
                                             </div>
                                         </div>
                                     @endforeach
                                 </div>
                             @endif

                            @if(count($sync['internships']['warnings'] ?? []) > 0)
                                 <div class="list-group mb-4">
                                     <div class="list-group-item bg-light text-uppercase fw-bold text-secondary py-2" style="font-size: 0.75rem;">
                                         <div class="row g-2">
                                             <div class="col-2 col-sm-1">Linha</div>
                                             <div class="col-4 col-sm-4">Estagiário</div>
                                             <div class="col-6 col-sm-7">Aviso</div>
                                         </div>
                                     </div>
                                     @foreach($sync['internships']['warnings'] as $warn)
                                         <div class="list-group-item py-2">
                                             <div class="row g-2 align-items-center">
                                                 <div class="col-2 col-sm-1">
                                                     <span class="badge bg-secondary rounded-pill">#{{ $warn['line'] ?? '-' }}</span>
                                                 </div>
                                                 <div class="col-4 col-sm-4 fw-semibold text-dark text-truncate" title="{{ $warn['student'] ?? '-' }}">
                                                     {{ $warn['student'] ?? '-' }}
                                                 </div>
                                                 <div class="col-6 col-sm-7">
                                                     <div class="bg-warning-subtle text-dark border border-warning-subtle rounded px-2 py-1 text-wrap">
                                                         <i class="bi bi-info-circle me-1"></i> {{ $warn['message'] ?? '-' }}
                                                     </div>
                                                 </div>
                                             </div>
                                         </div>
                                     @endforeach
                                 </div>
                             @endif

                            {{-- Avaliações --}}
                            <h6 class="mt-4">Avaliações sincronizadas</h6>
                            @php $processedEvaluations = $sync['evaluations']['processed'] ?? 0; @endphp
                            @if($processedEvaluations === 0)
                                <p>Nenhuma nova avaliação importada.</p>
                            @elseif($processedEvaluations === 1)
                                <p>1 avaliação importada com sucesso.</p>
                            @else
                                <p>{{ $processedEvaluations }} avaliações importadas com sucesso.</p>
                            @endif
                            @if(count($sync['evaluations']['errors'] ?? []) > 0)
                                <div class="list-group mb-4">
                                    <div class="list-group-item bg-light text-uppercase fw-bold text-secondary py-2" style="font-size: 0.75rem;">
                                        <div class="row g-2">
                                            <div class="col-2 col-sm-1">Linha</div>
                                            <div class="col-4 col-sm-4">Estagiário</div>
                                            <div class="col-6 col-sm-7">Motivo do Erro</div>
                                        </div>
                                    </div>
                                    @foreach($sync['evaluations']['errors'] as $err)
                                        <div class="list-group-item py-2">
                                            <div class="row g-2 align-items-center">
                                                <div class="col-2 col-sm-1">
                                                    <span class="badge bg-secondary rounded-pill">#{{ $err['line'] ?? '-' }}</span>
                                                </div>
                                                <div class="col-4 col-sm-4 fw-semibold text-dark text-truncate" title="{{ $err['student'] ?? '-' }}">
                                                    {{ $err['student'] ?? '-' }}
                                                </div>
                                                <div class="col-6 col-sm-7">
                                                    <div class="bg-danger-subtle text-danger border border-danger-subtle rounded px-2 py-1 text-wrap">
                                                        <i class="bi bi-exclamation-triangle me-1"></i> {{ $err['reason'] ?? '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if(count($sync['evaluations']['warnings'] ?? []) > 0)
                                <div class="list-group mb-4">
                                    <div class="list-group-item bg-light text-uppercase fw-bold text-secondary py-2" style="font-size: 0.75rem;">
                                        <div class="row g-2">
                                            <div class="col-2 col-sm-1">Linha</div>
                                            <div class="col-4 col-sm-4">Estagiário</div>
                                            <div class="col-6 col-sm-7">Aviso</div>
                                        </div>
                                    </div>
                                    @foreach($sync['evaluations']['warnings'] as $warn)
                                        <div class="list-group-item py-2">
                                            <div class="row g-2 align-items-center">
                                                <div class="col-2 col-sm-1">
                                                    <span class="badge bg-secondary rounded-pill">#{{ $warn['line'] ?? '-' }}</span>
                                                </div>
                                                <div class="col-4 col-sm-4 fw-semibold text-dark text-truncate" title="{{ $warn['student'] ?? '-' }}">
                                                    {{ $warn['student'] ?? '-' }}
                                                </div>
                                                <div class="col-6 col-sm-7">
                                                    <div class="bg-warning-subtle text-dark border border-warning-subtle rounded px-2 py-1 text-wrap">
                                                         <i class="bi bi-info-circle me-1"></i> {{ $warn['message'] ?? '-' }}
                                                     </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Form --}}
                            @if(isset($sync['form']) && $sync['form'] !== 'disabled')
                                <h6 class="mt-4">Sincronização do Formulário</h6>
                                @if($sync['form'] === 'updated')
                                    <p class="text-success mb-0"><i class="bi bi-check-circle me-1"></i> Lista de orientadores atualizada com sucesso no formulário do Google.</p>
                                @elseif($sync['form'] === 'up_to_date')
                                    <p class="text-secondary mb-0"><i class="bi bi-info-circle me-1"></i> A lista de orientadores no formulário já estava idêntica ao banco de dados.</p>
                                @elseif($sync['form'] === 'error')
                                    <p class="text-danger mb-0"><i class="bi bi-exclamation-triangle me-1"></i> Ocorreu uma falha ao tentar atualizar a lista de orientadores no formulário.</p>
                                @endif
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var myModal = new bootstrap.Modal(document.getElementById('syncModal'));
                    myModal.show();
                });
            </script>
        @endif
    @endif
@endsection
