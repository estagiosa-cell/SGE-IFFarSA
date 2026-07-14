@extends('layouts.auth')

@section('title', 'Relatórios do Sistema')

@section('main-content')
<div class="container-fluid mt-4 mx-1">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Relatórios do Sistema</h2>
    </div>

    {{-- Filtros Globais --}}
    <div class="d-md-none mb-2">
        <button class="btn btn-outline-primary btn-lg w-100 d-flex align-items-center justify-content-center gap-2"
            type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse" aria-expanded="false"
            aria-controls="filtersCollapse">
            <i class="bi bi-funnel"></i>
            <span>Filtros Globais</span>
            @if(request()->hasAny(['start_date', 'end_date', 'course_id', 'status']))
                <span class="badge text-bg-primary">Ativos</span>
            @endif
        </button>
    </div>

    <div class="collapse d-md-block" id="filtersCollapse">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('admin.reports.index') }}">
                    <div class="row m-0 g-2 align-items-end">
                        <div class="col-md-2">
                            <label for="start_date" class="form-label mb-0 small">Data Inicial</label>
                            <input type="date" class="form-control form-control-sm" id="start_date" name="start_date"
                                value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="end_date" class="form-label mb-0 small">Data Final</label>
                            <input type="date" class="form-control form-control-sm" id="end_date" name="end_date"
                                value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="course_id" class="form-label mb-0 small">Curso</label>
                            <select class="form-select form-select-sm" id="course_id" name="course_id">
                                <option value="">Todos os Cursos</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label mb-0 small">Situação</label>
                            <select class="form-select form-select-sm" id="status" name="status">
                                <option value="">Todas as Situações</option>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <div class="d-flex gap-1">
                                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                    <i class="bi bi-search"></i> Filtrar
                                </button>
                                <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm" title="Limpar Filtros">
                                    <i class="bi bi-eraser"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Tabs de Navegação --}}
    <ul class="nav nav-tabs mb-4 mx-2 border-bottom-0" id="reportsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-semibold" id="tab-fluxo-doc" data-bs-toggle="tab" data-bs-target="#fluxo-doc" type="button" role="tab" aria-selected="true">
                <i class="bi bi-file-earmark-check me-1"></i> Fluxo Documental
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="tab-acompanhamento" data-bs-toggle="tab" data-bs-target="#acompanhamento" type="button" role="tab" aria-selected="false">
                <i class="bi bi-kanban me-1"></i> Acompanhamento
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="tab-desempenho" data-bs-toggle="tab" data-bs-target="#desempenho" type="button" role="tab" aria-selected="false">
                <i class="bi bi-graph-up-arrow me-1"></i> Desempenho
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="tab-cursos" data-bs-toggle="tab" data-bs-target="#cursos" type="button" role="tab" aria-selected="false">
                <i class="bi bi-buildings me-1"></i> Relatórios por Curso
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold text-secondary" id="tab-historico" data-bs-toggle="tab" data-bs-target="#historico" type="button" role="tab" aria-selected="false">
                <i class="bi bi-clock-history me-1"></i> Histórico Global
            </button>
        </li>
    </ul>

    <div class="tab-content" id="reportsTabContent">

        {{-- ================================================================ --}}
        {{-- ABA 1: FLUXO DOCUMENTAL                                          --}}
        {{-- ================================================================ --}}
        <div class="tab-pane fade show active" id="fluxo-doc" role="tabpanel" aria-labelledby="tab-fluxo-doc">

            {{-- KPIs Fluxo Documental --}}
            <div class="row m-0 mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card border-start border-primary border-4 shadow-sm h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Tramitação Média</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $processingMetrics['avg'] }} <small class="text-muted fs-6">dias</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-start border-success border-4 shadow-sm h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Encaminhados no Prazo</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $onTimeForwarding['percent'] }}%
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-start border-info border-4 shadow-sm h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">Tramitação (Mediana)</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $processingMetrics['median'] }} <small class="text-muted fs-6">dias</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-start border-warning border-4 shadow-sm h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">Estágios Cancelados</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $cancellations->sum('total') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gráficos: Situação + Cancelamentos --}}
            <div class="row m-0">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="m-0 fw-bold text-primary">Situação Geral dos Estágios</h6>
                        </div>
                        <div class="card-body">
                            <div id="statusChart" style="width: 100%; height: 350px;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="m-0 fw-bold text-primary">Cancelamentos por Motivo</h6>
                        </div>
                        <div class="card-body">
                            <div id="cancellationsChart" style="width: 100%; height: 350px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gráficos: Prazo (Pizza) + Tempo Mensal (Linha) --}}
            <div class="row m-0">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="m-0 fw-bold text-primary">Encaminhamento dentro do Prazo</h6>
                        </div>
                        <div class="card-body">
                            <div id="onTimeChart" style="width: 100%; height: 300px;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="m-0 fw-bold text-primary">Tempo Médio de Tramitação por Mês</h6>
                        </div>
                        <div class="card-body">
                            <div id="monthlyChart" style="width: 100%; height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Encaminhamento por Curso --}}
            <div class="row m-0">
                <div class="col-lg-12 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="m-0 fw-bold text-primary">Encaminhamento dentro do Prazo por Curso</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                                <div class="list-group-item bg-light text-muted fw-bold small text-uppercase sticky-top">
                                    <div class="row m-0">
                                        <div class="col-4">Curso</div>
                                        <div class="col-3 text-end">Dentro do Prazo</div>
                                        <div class="col-3 text-end">Fora do Prazo</div>
                                        <div class="col-2 text-end">% Conforme</div>
                                    </div>
                                </div>
                                @forelse($onTimeByCourse as $item)
                                    @php
                                        $courseTotal = $item['on_time'] + $item['late'];
                                        $coursePercent = $courseTotal > 0 ? round(($item['on_time'] / $courseTotal) * 100, 1) : 0;
                                    @endphp
                                    <div class="list-group-item py-3">
                                        <div class="row m-0 align-items-center">
                                            <div class="col-4 text-truncate fw-semibold text-dark">{{ $item['course_name'] }}</div>
                                            <div class="col-3 text-end">
                                                <span class="badge bg-success rounded-pill px-3">{{ $item['on_time'] }}</span>
                                            </div>
                                            <div class="col-3 text-end">
                                                <span class="badge bg-danger rounded-pill px-3">{{ $item['late'] }}</span>
                                            </div>
                                            <div class="col-2 text-end fw-bold {{ $coursePercent >= 80 ? 'text-success' : 'text-danger' }}">
                                                {{ $coursePercent }}%
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="list-group-item text-center text-muted py-4">Nenhum dado de encaminhamento disponível.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            {{-- Tempo de Tramitação Detalhado --}}
            <div class="row m-0">
                <div class="col-lg-12 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="m-0 fw-bold text-primary">Tempo de Tramitação por Estágio (Dias até a Liberação)</h6>
                            <span class="badge bg-secondary">Mínimo: {{ $processingMetrics['min'] }} dias | Máximo: {{ $processingMetrics['max'] }} dias</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                                <div class="list-group-item bg-light text-muted fw-bold small text-uppercase sticky-top">
                                    <div class="row m-0">
                                        <div class="col-8">Estagiário</div>
                                        <div class="col-4 text-end">Dias Decorridos</div>
                                    </div>
                                </div>
                                @forelse($processingTimes as $time)
                                    <div class="list-group-item py-3">
                                        <div class="row m-0 align-items-center">
                                            <div class="col-8 text-truncate fw-semibold text-dark">
                                                {{ $time->student_name }}
                                            </div>
                                            <div class="col-4 text-end">
                                                @php
                                                    $color = $time->days_to_release > 30 ? 'bg-danger' : ($time->days_to_release > 15 ? 'bg-warning text-dark' : 'bg-success');
                                                @endphp
                                                <span class="badge {{ $color }} rounded-pill px-3">{{ $time->days_to_release }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="list-group-item text-center text-muted py-4">Nenhum dado encontrado no período.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- ABA 2: ACOMPANHAMENTO DA PRÁTICA                                --}}
        {{-- ================================================================ --}}
        <div class="tab-pane fade" id="acompanhamento" role="tabpanel" aria-labelledby="tab-acompanhamento">

            {{-- KPIs Acompanhamento --}}
            <div class="row m-0 mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card border-start border-success border-4 shadow-sm h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Concluídos no Prazo</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $completionOnTime['percent'] }}%
                            </div>
                            <small class="text-muted">{{ $completionOnTime['on_time'] }} de {{ $completionOnTime['total'] }} estágios</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-start border-primary border-4 shadow-sm h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Concluídos</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $completionOnTime['total'] }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-start border-danger border-4 shadow-sm h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">Concluídos Fora do Prazo</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $completionOnTime['late'] }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-start border-info border-4 shadow-sm h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">Total de Aditivos</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $amendmentsCount }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gráfico de conclusão + aditivos --}}
            <div class="row m-0">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="m-0 fw-bold text-primary">Conclusão dentro do Prazo</h6>
                        </div>
                        <div class="card-body">
                            <div id="completionChart" style="width: 100%; height: 300px;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="m-0 fw-bold text-primary">Estágios Aditivados</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item bg-light text-muted fw-bold small text-uppercase sticky-top">
                                    <div class="row m-0">
                                        <div class="col-8">Motivo</div>
                                        <div class="col-4 text-end">Quantidade</div>
                                    </div>
                                </div>
                                @forelse($amendmentsByReason as $amendment)
                                    <div class="list-group-item py-3">
                                        <div class="row m-0 align-items-center">
                                            <div class="col-8 fw-semibold text-dark">{{ $amendment['reason'] }}</div>
                                            <div class="col-4 text-end">
                                                <span class="badge bg-primary rounded-pill px-3">{{ $amendment['total'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="list-group-item text-center text-muted py-4">
                                        <i class="bi bi-clipboard-check text-success fs-3 d-block mb-2"></i>
                                        Nenhum aditivo registrado no período.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- ABA 3: DESEMPENHO                                                --}}
        {{-- ================================================================ --}}
        <div class="tab-pane fade" id="desempenho" role="tabpanel" aria-labelledby="tab-desempenho">

            {{-- KPIs Desempenho --}}
            <div class="row m-0 mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card border-start border-primary border-4 shadow-sm h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Média Geral</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $globalGradeMetrics['avg'] !== null ? $globalGradeMetrics['avg'] : '—' }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-start border-success border-4 shadow-sm h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Nota Máxima</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $globalGradeMetrics['max'] !== null ? $globalGradeMetrics['max'] : '—' }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-start border-danger border-4 shadow-sm h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">Nota Mínima</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $globalGradeMetrics['min'] !== null ? $globalGradeMetrics['min'] : '—' }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-start border-info border-4 shadow-sm h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">Total Avaliados</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $globalGradeMetrics['total'] }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Histograma de Notas --}}
            <div class="row m-0">
                <div class="col-lg-12 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="m-0 fw-bold text-primary">Distribuição das Notas</h6>
                        </div>
                        <div class="card-body">
                            <div id="gradeHistogram" style="width: 100%; height: 350px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notas por Curso --}}
            <div class="row m-0">
                <div class="col-lg-12 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="m-0 fw-bold text-primary">Desempenho (Notas Médias) por Curso</h6>
                        </div>
                        <div class="card-body">
                            <div id="gradesChart" style="width: 100%; height: 350px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- ABA 4: RELATÓRIOS POR CURSO                                      --}}
        {{-- ================================================================ --}}
        <div class="tab-pane fade" id="cursos" role="tabpanel" aria-labelledby="tab-cursos">

            {{-- Gráfico Distribuição por Curso --}}
            <div class="row m-0">
                <div class="col-lg-12 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="m-0 fw-bold text-primary">Distribuição de Estagiários por Curso</h6>
                        </div>
                        <div class="card-body">
                            <div id="coursesChart" style="width: 100%; height: 400px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Concedentes por Curso + Top Concedentes --}}
            <div class="row m-0">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="m-0 fw-bold text-primary">Concedentes por Curso</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                                <div class="list-group-item bg-light text-muted fw-bold small text-uppercase sticky-top">
                                    <div class="row m-0">
                                        <div class="col-5">Curso</div>
                                        <div class="col-3 text-end">Concedentes</div>
                                        <div class="col-4 text-end">Estagiários</div>
                                    </div>
                                </div>
                                @forelse($companiesByCourse as $item)
                                    <div class="list-group-item py-3">
                                        <div class="row m-0 align-items-center">
                                            <div class="col-5 text-truncate fw-semibold text-dark">{{ $item->course_name }}</div>
                                            <div class="col-3 text-end">
                                                <span class="badge bg-info rounded-pill px-3">{{ $item->total_concedentes }}</span>
                                            </div>
                                            <div class="col-4 text-end">
                                                <span class="badge bg-primary rounded-pill px-3">{{ $item->total_estagiarios }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="list-group-item text-center text-muted py-4">Nenhum dado encontrado.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="m-0 fw-bold text-primary">Top Concedentes (Empresas)</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                                <div class="list-group-item bg-light text-muted fw-bold small text-uppercase sticky-top">
                                    <div class="row m-0">
                                        <div class="col-8">Empresa</div>
                                        <div class="col-4 text-end">Estagiários</div>
                                    </div>
                                </div>
                                @forelse($topCompanies as $company)
                                    <div class="list-group-item py-3">
                                        <div class="row m-0 align-items-center">
                                            <div class="col-8 text-truncate fw-semibold text-dark" title="{{ $company->company_name }}">
                                                {{ $company->company_name ?: 'Não Informado' }}
                                            </div>
                                            <div class="col-4 text-end">
                                                <span class="badge bg-primary rounded-pill px-3">{{ $company->total_estagios }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="list-group-item text-center text-muted py-4">Nenhum dado encontrado no período.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Supervisores por Curso + Orientadores --}}
            <div class="row m-0">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="m-0 fw-bold text-primary">Supervisores por Curso</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                                <div class="list-group-item bg-light text-muted fw-bold small text-uppercase sticky-top">
                                    <div class="row m-0">
                                        <div class="col-5">Curso</div>
                                        <div class="col-3 text-end">Supervisores</div>
                                        <div class="col-4 text-end">Média/Supervisor</div>
                                    </div>
                                </div>
                                @forelse($supervisorsMetrics as $sup)
                                    <div class="list-group-item py-3">
                                        <div class="row m-0 align-items-center">
                                            <div class="col-5 text-truncate fw-semibold text-dark">{{ $sup->course_name }}</div>
                                            <div class="col-3 text-end">
                                                <span class="badge bg-secondary rounded-pill px-3">{{ $sup->total_supervisores }}</span>
                                            </div>
                                            <div class="col-4 text-end text-muted">
                                                {{ $sup->total_supervisores > 0 ? round($sup->total_estagiarios / $sup->total_supervisores, 1) : 0 }}
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="list-group-item text-center text-muted py-4">Nenhum supervisor vinculado.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="m-0 fw-bold text-primary">Carga de Orientação por Docente</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                                <div class="list-group-item bg-light text-muted fw-bold small text-uppercase sticky-top">
                                    <div class="row m-0">
                                        <div class="col-8">Orientador</div>
                                        <div class="col-4 text-end">Estagiários</div>
                                    </div>
                                </div>
                                @forelse($advisorsMetrics as $advisor)
                                    <div class="list-group-item py-3">
                                        <div class="row m-0 align-items-center">
                                            <div class="col-8 text-truncate fw-semibold text-dark">
                                                {{ $advisor->advisor_name }}
                                            </div>
                                            <div class="col-4 text-end">
                                                <span class="badge bg-primary rounded-pill px-3">{{ $advisor->total_estagiarios }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="list-group-item text-center text-muted py-4">Nenhum orientador vinculado.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- ABA 5: HISTÓRICO GLOBAL                                          --}}
        {{-- ================================================================ --}}
        <div class="tab-pane fade" id="historico" role="tabpanel" aria-labelledby="tab-historico">
            <div class="row m-0">
                <div class="col-12 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="m-0 fw-bold text-primary">Auditoria: Log de Atividades Recentes</h6>
                            <span class="badge bg-secondary">Últimas {{ $globalActivities->total() }} registradas</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @forelse($globalActivities as $activity)
                                    <div class="list-group-item py-3">
                                        <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                            <div class="d-flex align-items-center gap-2">
                                                @if($activity->description === 'Estágio cancelado com motivo' || $activity->description === 'deleted')
                                                    <span class="badge bg-danger rounded-circle p-2"><i class="bi bi-x-circle text-white"></i></span>
                                                @elseif($activity->description === 'Documento gerado')
                                                    <span class="badge bg-info rounded-circle p-2"><i class="bi bi-file-earmark-text text-white"></i></span>
                                                @elseif($activity->description === 'created')
                                                    <span class="badge bg-success rounded-circle p-2"><i class="bi bi-plus text-white"></i></span>
                                                @else
                                                    <span class="badge bg-primary rounded-circle p-2"><i class="bi bi-pencil text-white"></i></span>
                                                @endif
                                                <h6 class="mb-0 fw-bold text-dark">{{ ucfirst($activity->description) }}</h6>
                                            </div>
                                            <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ $activity->created_at->diffForHumans() }}</small>
                                        </div>
                                        <div class="ps-5 text-muted small">
                                            <span class="fw-semibold">Por:</span> {{ $activity->causer ? $activity->causer->name : 'Sistema' }}
                                            <br>
                                            <span class="fw-semibold">Registro:</span> {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}

                                            @if($activity->properties->isNotEmpty())
                                                <div class="mt-2 bg-light p-2 rounded border">
                                                    @foreach($activity->properties as $key => $value)
                                                        @if(is_array($value))
                                                            <div class="text-truncate"><strong>{{ $key }}:</strong> {{ json_encode($value) }}</div>
                                                        @else
                                                            <div class="text-truncate"><strong>{{ $key }}:</strong> {{ $value }}</div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="list-group-item text-center text-muted py-5">
                                        <i class="bi bi-clock-history fs-1 d-block mb-3 text-secondary"></i>
                                        Nenhuma atividade registrada no sistema ainda.
                                    </div>
                                @endforelse
                            </div>
            <div class="card-footer bg-white py-3 border-top">
                {{ $globalActivities->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const initReportsDashboard = function () {
        // Resize em todos os charts ao trocar de aba
        var allCharts = [];
        var tabEls = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabEls.forEach(function(tabEl) {
            tabEl.addEventListener('shown.bs.tab', function () {
                setTimeout(() => {
                    allCharts.forEach(c => { if(c) c.resize(); });
                }, 200);
            });
        });

        const emptyHtml = '<div class="text-center text-muted py-5 d-flex flex-column justify-content-center h-100"><i class="bi bi-inbox text-secondary fs-1 d-block mb-2 opacity-25"></i><span class="small">Nenhum dado para exibir</span></div>';

        const renderChart = (id, option, hasData) => {
            const dom = document.getElementById(id);
            if (!dom) return null;
            if (!hasData) {
                dom.innerHTML = emptyHtml;
                return null;
            }
            dom.innerHTML = '';
            const instance = window.echarts.init(dom);
            instance.setOption(option);
            allCharts.push(instance);
            return instance;
        };

        // ===========================
        // Gráficos de Fluxo Documental
        // ===========================
        
        // Status Atual (Barras)
        renderChart('statusChart', {
            tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
            grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
            xAxis: { type: 'value', boundaryGap: [0, 0.01] },
            yAxis: { type: 'category', data: {!! json_encode($internshipStatuses->pluck('status')) !!} },
            series: [{
                name: 'Quantidade',
                type: 'bar',
                data: {!! json_encode($internshipStatuses->pluck('total')) !!},
                itemStyle: { color: '#0d6efd' }
            }]
        }, {{ $internshipStatuses->count() > 0 ? 'true' : 'false' }});

        // Cancelamentos (Pizza)
        renderChart('cancellationsChart', {
            tooltip: { trigger: 'item' },
            legend: { top: 'bottom' },
            series: [{
                name: 'Motivo',
                type: 'pie',
                radius: ['40%', '70%'],
                itemStyle: { borderRadius: 10, borderColor: '#fff', borderWidth: 2 },
                data: {!! json_encode($cancellations->map(fn($c) => ['name' => $c->motivo, 'value' => $c->total])) !!}
            }]
        }, {{ $cancellations->count() > 0 ? 'true' : 'false' }});

        // Encaminhamento no Prazo (Pizza)
        renderChart('onTimeChart', {
            tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
            legend: { top: 'bottom' },
            color: ['#198754', '#dc3545'],
            series: [{
                name: 'Prazo',
                type: 'pie',
                radius: '70%',
                data: [
                    { value: {{ $onTimeForwarding['on_time'] ?? 0 }}, name: 'Dentro do Prazo' },
                    { value: {{ $onTimeForwarding['late'] ?? 0 }}, name: 'Fora do Prazo' }
                ]
            }]
        }, {{ ($onTimeForwarding['total'] ?? 0) > 0 ? 'true' : 'false' }});

        // Tempo Mensal (Linha)
        renderChart('monthlyChart', {
            tooltip: { trigger: 'axis' },
            xAxis: { type: 'category', data: {!! json_encode($monthlyProcessing->pluck('month')) !!} },
            yAxis: { type: 'value', name: 'Dias' },
            series: [{
                name: 'Média de Dias',
                type: 'line',
                smooth: true,
                data: {!! json_encode($monthlyProcessing->pluck('avg_days')) !!},
                areaStyle: { opacity: 0.1 },
                itemStyle: { color: '#0dcaf0' }
            }]
        }, {{ $monthlyProcessing->count() > 0 ? 'true' : 'false' }});

        // ===========================
        // Gráficos de Acompanhamento
        // ===========================

        var completionData = @json($completionOnTime);
        renderChart('completionChart', {
            tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
            legend: { bottom: 'bottom' },
            color: ['#198754', '#dc3545'],
            series: [{
                name: 'Conclusão', type: 'pie', radius: ['40%', '65%'],
                label: { formatter: '{b}\n{d}%' },
                data: [
                    { value: completionData.on_time, name: 'No Prazo' },
                    { value: completionData.late, name: 'Fora do Prazo / Com Aditivo' }
                ]
            }]
        }, completionData.total > 0);

        // ===========================
        // ABA 3: DESEMPENHO
        // ===========================

        // Histograma de notas
        var gradeDistData = @json($gradeDistribution);
        var gradeDistSum = gradeDistData.reduce((a, i) => a + parseInt(i.total || 0), 0);
        renderChart('gradeHistogram', {
            tooltip: { trigger: 'axis', formatter: '{b}: <b>{c} estágios</b>' },
            grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
            xAxis: { type: 'category', data: gradeDistData.map(i => i.faixa) },
            yAxis: { type: 'value', name: 'Quantidade' },
            series: [{
                name: 'Estágios', type: 'bar',
                data: gradeDistData.map(i => parseInt(i.total || 0)),
                itemStyle: {
                    color: function(params) {
                        var colors = ['#198754', '#20c997', '#0dcaf0', '#ffc107', '#fd7e14', '#dc3545'];
                        return colors[params.dataIndex % colors.length];
                    },
                    borderRadius: [4, 4, 0, 0]
                }
            }]
        }, gradeDistSum > 0);

        // Notas por Curso (Bar)
        var gradesData = @json($averageGrades);
        var hasGrades = gradesData.some(i => i.nota_media !== null && i.nota_media > 0);
        renderChart('gradesChart', {
            tooltip: { trigger: 'axis', formatter: '{b}: <b>{c}</b>' },
            grid: { left: '3%', right: '4%', bottom: '15%', containLabel: true },
            xAxis: { type: 'category', data: gradesData.map(i => i.course_name), axisLabel: { rotate: 30, hideOverlap: true } },
            yAxis: { type: 'value', name: 'Nota Média', max: 10 },
            series: [{
                data: gradesData.map(i => parseFloat(i.nota_media || 0)),
                type: 'bar',
                itemStyle: { color: '#198754', borderRadius: [4, 4, 0, 0] }
            }]
        }, hasGrades);

        // ===========================
        // ABA 4: RELATÓRIOS POR CURSO
        // ===========================

        var coursesData = @json($internshipsByCourse);
        var coursesSum = coursesData.reduce((a, i) =>
            a + parseInt(i.total_em_andamento || 0) + parseInt(i.total_liberado || 0) +
            parseInt(i.total_concluido || 0) + parseInt(i.total_cancelado || 0), 0);
        renderChart('coursesChart', {
            tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
            legend: { data: ['Liberado', 'Em Andamento', 'Concluído', 'Cancelado'], bottom: 0 },
            grid: { left: '3%', right: '4%', bottom: '15%', containLabel: true },
            xAxis: { type: 'value' },
            yAxis: { type: 'category', data: coursesData.map(i => i.course_name), axisLabel: { hideOverlap: true, width: 120, overflow: 'truncate' } },
            series: [
                { name: 'Liberado', type: 'bar', stack: 'total', itemStyle: { color: '#6c757d' }, data: coursesData.map(i => parseInt(i.total_liberado || 0)) },
                { name: 'Em Andamento', type: 'bar', stack: 'total', itemStyle: { color: '#0d6efd' }, data: coursesData.map(i => parseInt(i.total_em_andamento || 0)) },
                { name: 'Concluído', type: 'bar', stack: 'total', itemStyle: { color: '#198754' }, data: coursesData.map(i => parseInt(i.total_concluido || 0)) },
                { name: 'Cancelado', type: 'bar', stack: 'total', itemStyle: { color: '#dc3545' }, data: coursesData.map(i => parseInt(i.total_cancelado || 0)) }
            ]
        }, coursesSum > 0);

        // Resize global
        window.addEventListener('resize', () => {
            allCharts.forEach(c => { if(c) c.resize(); });
        });
    };

    // Precisamos garantir duas coisas:
    // 1. O DOM está carregado
    // 2. O Vite carregou o ECharts
    let domReady = document.readyState !== 'loading';
    let echartsReady = !!window.echarts;

    const tryInit = () => {
        if (domReady && echartsReady) {
            initReportsDashboard();
        }
    };

    if (!domReady) {
        document.addEventListener('DOMContentLoaded', () => {
            domReady = true;
            tryInit();
        });
    }

    if (!echartsReady) {
        window.addEventListener('echartsLoaded', () => {
            echartsReady = true;
            tryInit();
        });
    }

    tryInit();
</script>
@endpush
