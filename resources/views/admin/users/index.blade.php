@extends('layouts.auth')

@section('title', 'Gerenciar Usuários')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Gerenciar Usuários</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.users.index', array_merge(request()->except('show_deleted'), ['show_deleted' => $showDeleted ? 0 : 1])) }}"
                    class="btn btn-outline-{{ $showDeleted ? 'secondary' : 'danger' }}">
                    <i class="bi bi-trash{{ $showDeleted ? '' : '-fill' }} me-2"></i>
                    {{ $showDeleted ? 'Ver Ativos' : 'Ver Deletados' }}
                </a>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Novo Usuário
                </a>
            </div>
        </div>

        {{-- Filtros de Pesquisa --}}
        <div class="d-md-none mb-2">
            <button class="btn btn-outline-primary btn-lg w-100 d-flex align-items-center justify-content-center gap-2"
                type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse" aria-expanded="false"
                aria-controls="filtersCollapse">
                <i class="bi bi-funnel"></i>
                <span>Filtrar Usuários</span>
                @if ($activeFiltersCount > 0)
                    <span class="badge text-bg-primary">{{ $activeFiltersCount }}</span>
                @endif
            </button>
        </div>

        <div class="collapse d-md-block" id="filtersCollapse">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('admin.users.index') }}">
                        <div class="row m-0 g-2 align-items-end">
                            <div class="col-md-4 col-lg-4">
                                <label for="search" class="form-label mb-0 small">Buscar</label>
                                <input type="text" class="form-control form-control-sm" id="search" name="search"
                                    value="{{ request('search') }}" placeholder="Nome ou e-mail">
                            </div>

                            <div class="col-md-2 col-lg-2">
                                <label for="role" class="form-label mb-0 small">Papel</label>
                                <select class="form-select form-select-sm" id="role" name="role">
                                    <option value="">Todos</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->value }}"
                                            {{ request('role') == $role->value ? 'selected' : '' }}>
                                            {{ $role->label() ?? $role->value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 col-lg-3">
                                <label for="status" class="form-label mb-0 small">Status</label>
                                <select class="form-select form-select-sm" id="status" name="status">
                                    <option value="">Todos</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Ativo
                                    </option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                                        Desativado
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-3 col-lg-3">
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-outline-primary btn-sm flex-fill">
                                        <i class="bi bi-funnel"></i> Filtrar
                                    </button>
                                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if ($users->isEmpty())
            <x-ui.empty-state 
                icon="{{ $showDeleted ? 'bi-trash' : (request()->hasAny(['search', 'role', 'status']) ? 'bi-search' : 'bi-person-x') }}"
                title="{{ $showDeleted ? 'Nenhum usuário deletado' : (request()->hasAny(['search', 'role', 'status']) ? 'Nenhum usuário encontrado' : 'Nenhum usuário cadastrado') }}"
                description="{!! $showDeleted ? 'Não há usuários deletados no momento.<br>Você pode alternar para ver os ativos.' : (request()->hasAny(['search', 'role', 'status']) ? 'Não foram encontrados usuários com os filtros aplicados.<br>Tente ajustar os critérios de busca.' : 'Ainda não existem usuários cadastrados.<br>Comece adicionando o primeiro usuário do sistema.') !!}"
            >
                @if ($showDeleted)
                    <a href="{{ route('admin.users.index', array_merge(request()->except('show_deleted'), ['show_deleted' => 0])) }}"
                        class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>Ver Usuários Ativos
                    </a>
                @elseif (request()->hasAny(['search', 'role', 'status']))
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>Ver Todos os Usuários
                    </a>
                @else
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-plus-circle me-2"></i>Cadastrar Usuário
                    </a>
                @endif
            </x-ui.empty-state>
        @else
            <div class="text-muted small mb-3">
                Mostrando de <strong>{{ $users->firstItem() }}</strong> a <strong>{{ $users->lastItem() }}</strong> de <strong>{{ $users->total() }}</strong> resultados
            </div>
            @foreach ($users as $user)
                <div class="card mb-3 shadow-sm border-0">
                    <div class="card-body py-3 px-4">
                        <div class="row m-0 align-items-center g-0">
                            <div class="col-md-3 fw-bold text-dark">{{ $user->name }}</div>
                            <div class="col-md-3 small text-muted">{{ $user->email }}</div>
                            <div class="col-md-2">
                                <span class="badge bg-secondary">{{ $user->role->label() ?? $user->role }}</span>
                            </div>
                            <div class="col-md-2">
                                @if ($user->isActive())
                                    <span class="badge bg-success">Ativo</span>
                                @else
                                    <span class="badge bg-danger">Desativado</span>
                                @endif
                            </div>
                            <div class="col-md-2 text-end">
                                @if ($showDeleted)
                                    <form method="POST" action="{{ route('admin.users.restore', $user->id) }}"
                                        class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-sm px-3 py-1" title="Restaurar">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Restaurar
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.users.edit', $user->id) }}"
                                        class="btn btn-secondary btn-sm px-3 py-1">
                                        <i class="bi bi-pencil me-1"></i>Editar
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Paginação --}}
            <div class="mt-3">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection
