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
                                <label for="search" class="form-label mb-0 small">Buscar (E-mail ou Aluno)</label>
                                <input type="text" class="form-control form-control-sm" id="search" name="search"
                                    value="{{ request('search') }}" placeholder="Ex: joao@email.com ou João Silva">
                            </div>

                            <div class="col-md-3 col-lg-3">
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-outline-primary btn-sm flex-fill">
                                        <i class="bi bi-search"></i> Buscar
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
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 ps-4">Destinatário</th>
                                <th class="border-0">Assunto / Tipo</th>
                                <th class="border-0">Estagiário</th>
                                <th class="border-0">Status</th>
                                <th class="border-0 pe-4">Data de Envio</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold">{{ $log->recipient }}</div>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $log->subject_type ?? 'N/D' }}</span>
                                    </td>
                                    <td>
                                        @if($log->internship)
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-person me-2 text-muted"></i>
                                                {{ $log->internship->student_name }}
                                            </div>
                                        @else
                                            <span class="text-muted fst-italic">Sem Vínculo</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->status)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Enviado</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">Falha</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-muted small">
                                        {{ $log->created_at->format('d/m/Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $logs->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection
