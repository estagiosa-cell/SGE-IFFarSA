@extends('layouts.auth')

@section('title', 'Editar Usuário')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Editar Usuário - {{ $user->name }}</h2>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left-circle me-2"></i>Voltar
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form class="needs-validation" action="{{ route('admin.users.update', $user->id) }}" method="POST"
                    novalidate>
                    @csrf
                    @method('PUT')
                    <div class="row">
                        {{-- Nome --}}
                        <div class="mb-3 col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', $user->name) }}"
                                    placeholder="Nome completo" required>
                                <label for="name"><i class="bi bi-person me-2"></i>Nome *</label>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">O campo nome é obrigatório.</div>
                                @enderror
                            </div>
                        </div>
                        {{-- E-mail --}}
                        <div class="mb-3 col-md-6">
                            <div class="form-floating">
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email', $user->email) }}"
                                    placeholder="E-mail" required>
                                <label for="email"><i class="bi bi-envelope me-2"></i>E-mail *</label>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">O campo e-mail é obrigatório.</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        {{-- Papel --}}
                        <div class="mb-3 col-md-6">
                            <div class="form-floating">
                                <select class="form-select @error('role') is-invalid @enderror" id="role"
                                    name="role" required>
                                    <option value="">Selecione o papel</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->value }}"
                                            {{ old('role', $user->role->value) == $role->value ? 'selected' : '' }}>
                                            {{ $role->label() ?? $role->value }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="role"><i class="bi bi-person-badge me-2"></i>Papel *</label>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">O campo papel é obrigatório.</div>
                                @enderror
                            </div>
                        </div>
                        {{-- Status --}}
                        <div class="mb-3 col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="status"
                                    value="{{ $user->isActive() ? 'Ativo' : 'Desativado' }}" disabled>
                                <label for="status"><i class="bi bi-activity me-2"></i>Status</label>
                            </div>
                        </div>
                    </div>

                    {{-- Informações adicionais --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body py-2">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar-plus me-1"></i>
                                        <strong>Criado em:</strong> {{ $user->created_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body py-2">
                                    <small class="text-muted">
                                        <i class="bi bi-pencil-square me-1"></i>
                                        <strong>Atualizado em:</strong> {{ $user->updated_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <x-form-info-alert>
                        <li>O e-mail deve ser válido e único no sistema</li>
                        <li>Mudanças no e-mail podem afetar notificações futuras</li>
                        <li>Alterações no papel afetarão as permissões do usuário</li>
                    </x-form-info-alert>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Salvar Alterações
                        </button>
                    </div>
                </form>
                <hr>
                <div class="d-flex gap-2 mt-3">
                    @if ($user->isActive())
                        {{-- Botão Desativar --}}
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                            data-bs-target="#deactivateModal">
                            <i class="bi bi-person-dash me-2"></i>Desativar Conta
                        </button>
                    @else
                        {{-- Botão Reativar --}}
                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                            data-bs-target="#reactivateModal">
                            <i class="bi bi-person-check me-2"></i>Reativar Conta
                        </button>
                    @endif
                    {{-- Botão Deletar --}}
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="bi bi-trash me-2"></i>Excluir Conta
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Desativar --}}
    <div class="modal fade" id="deactivateModal" tabindex="-1" aria-labelledby="deactivateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deactivateModalLabel">Confirmar Desativação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja desativar este usuário? Ele não poderá acessar o sistema até ser reativado.
                </div>
                <div class="modal-footer">
                    <form action="{{ route('admin.users.deactivate', $user->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-person-dash me-2"></i>Desativar
                        </button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Reativar --}}
    <div class="modal fade" id="reactivateModal" tabindex="-1" aria-labelledby="reactivateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reactivateModalLabel">Confirmar Reativação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja reativar este usuário? Ele poderá acessar o sistema novamente.
                </div>
                <div class="modal-footer">
                    <form action="{{ route('admin.users.reactivate', $user->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-person-check me-2"></i>Reativar
                        </button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Excluir --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja excluir este usuário? Esta ação pode ser desfeita.
                </div>
                <div class="modal-footer">
                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Excluir
                        </button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
@endsection
