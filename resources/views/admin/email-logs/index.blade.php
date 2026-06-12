@extends('layouts.auth')

@section('title', 'Logs de E-mails')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Logs de E-mails</h2>
        </div>

        {{-- Filtros de Pesquisa --}}
        <div class="d-md-none mb-2">
            <button class="btn btn-outline-primary btn-lg w-100 d-flex align-items-center justify-content-center gap-2"
                type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse" aria-expanded="false"
                aria-controls="filtersCollapse">
                <i class="bi bi-funnel"></i>
                <span>Filtrar Logs</span>
            </button>
        </div>

        <div class="collapse d-md-block" id="filtersCollapse">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('admin.email-logs.index') }}">
                        <div class="row m-0 g-2 align-items-end">
                            <div class="col-md-9 col-lg-9">
                                <label for="search" class="form-label mb-0 small">Buscar por nome</label>
                                <input type="text" class="form-control form-control-sm" id="search" name="search"
                                    value="{{ request('search') }}" placeholder="Nome do estagiário">
                            </div>

                            <div class="col-md-3 col-lg-3">
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-outline-primary btn-sm flex-fill">
                                        <i class="bi bi-funnel"></i> Filtrar
                                    </button>
                                    <a href="{{ route('admin.email-logs.index') }}"
                                        class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if ($logs->isEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            @if (request()->has('search') && request('search') != '')
                                <i class="bi bi-search text-muted display-3"></i>
                            @else
                                <i class="bi bi-envelope-paper text-muted display-3"></i>
                            @endif
                        </div>
                        @if (request()->has('search') && request('search') != '')
                            <h4 class="text-muted mb-3">Nenhum log encontrado</h4>
                            <p class="text-muted mb-4">
                                Não foram encontrados registros com os filtros aplicados.<br>
                                Tente ajustar os critérios de busca.
                            </p>
                            <a href="{{ route('admin.email-logs.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-2"></i>Ver Todos os Logs
                            </a>
                        @else
                            <h4 class="text-muted mb-3">Nenhum e-mail registrado</h4>
                            <p class="text-muted mb-4">
                                O sistema ainda não possui registros de e-mails enviados.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="text-muted small mb-3">
                Mostrando de <strong>{{ $logs->firstItem() }}</strong> a <strong>{{ $logs->lastItem() }}</strong> de <strong>{{ $logs->total() }}</strong> resultados
            </div>
            @foreach ($logs as $log)
                <div class="card mb-3 shadow-sm border-0">
                    <div class="card-body py-3 px-4">
                        <div class="row m-0 align-items-center g-0">
                            <div class="col-md-9">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <h6 class="fw-bold text-dark mb-0">
                                        @if($log->internship)
                                            {{ $log->internship->student_name }}
                                        @else
                                            Sem Vínculo
                                        @endif
                                    </h6>
                                    @if($log->status)
                                        <span class="badge bg-success">Enviado</span>
                                    @else
                                        <span class="badge bg-danger">Falha</span>
                                    @endif
                                </div>
                                <div class="row m-0 small text-muted">
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <i class="bi bi-tag me-1"></i>
                                            <strong>Tipo:</strong> {{ $log->subject_type ?? 'N/D' }}
                                        </div>
                                        <div class="mb-1">
                                            <i class="bi bi-calendar-plus me-1"></i>
                                            <strong>Enviado em:</strong> {{ $log->created_at->format('d/m/Y H:i') }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <i class="bi bi-envelope me-1"></i>
                                            <strong>Destinatário:</strong> {{ $log->recipient }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="mt-3">
                {{ $logs->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection
