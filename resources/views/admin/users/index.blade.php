@extends('layouts.auth')

@section('title', 'Gerenciar Usuários')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Gerenciar Usuários</h2>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Novo Usuário
            </a>
        </div>

        <!-- Filtros de Pesquisa -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('admin.users.index') }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label for="search" class="form-label mb-0 small">Buscar</label>
                            <input type="text" class="form-control form-control-sm" id="search" name="search"
                                value="{{ request('search') }}" placeholder="Nome ou e-mail">
                        </div>

                        <div class="col-md-3">
                            <label for="role" class="form-label mb-0 small">Papel</label>
                            <select class="form-select form-select-sm" id="role" name="role">
                                <option value="">Todos os Papéis</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->value }}"
                                        {{ request('role') == $role->value ? 'selected' : '' }}>
                                        {{ $role->label() ?? $role->value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="status" class="form-label mb-0 small">Status</label>
                            <select class="form-select form-select-sm" id="status" name="status">
                                <option value="">Todos os Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Ativo</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Desativado
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <div class="d-flex gap-1">
                                <button type="submit" class="btn btn-outline-primary btn-sm flex-fill">
                                    <i class="bi bi-funnel"></i> Filtrar
                                </button>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-arrow-clockwise"></i> Limpar Filtros
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if ($users->isEmpty())
            <div class="card">
                <div class="card-body">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            @if (request()->hasAny(['search', 'role', 'status']))
                                <i class="bi bi-search text-muted" style="font-size: 4rem;"></i>
                            @else
                                <i class="bi bi-person-x text-muted" style="font-size: 4rem;"></i>
                            @endif
                        </div>
                        @if (request()->hasAny(['search', 'role', 'status']))
                            <h4 class="text-muted mb-3">Nenhum usuário encontrado</h4>
                            <p class="text-muted mb-4">
                                Não foram encontrados usuários com os filtros aplicados.<br>
                                Tente ajustar os critérios de busca.
                            </p>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-2"></i>Ver Todos os Usuários
                            </a>
                        @else
                            <h4 class="text-muted mb-3">Nenhum usuário encontrado</h4>
                            <p class="text-muted mb-4">
                                Ainda não existem usuários cadastrados.<br>
                                Comece adicionando o primeiro usuário do sistema.
                            </p>
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-plus-circle me-2"></i>Cadastrar Usuário
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @else
            @foreach ($users as $user)
                <div class="card mb-2 shadow-sm border-0">
                    <div class="card-body py-2 px-3">
                        <div class="row align-items-center g-0">
                            <div class="col-md-3 fw-bold text-dark">{{ $user->name }}</div>
                            <div class="col-md-3 small">{{ $user->email }}</div>
                            <div class="col-md-2 small">{{ $user->role->label() ?? $user->role }}</div>
                            <div class="col-md-2">
                                @if ($user->isActive())
                                    <span class="badge bg-success">Ativo</span>
                                @else
                                    <span class="badge bg-danger">Desativado</span>
                                @endif
                            </div>
                            <div class="col-md-2 text-end">
                                <a href="{{ route('admin.users.edit', $user->id) }}"
                                    class="btn btn-secondary btn-sm px-3 py-1">
                                    <i class="bi bi-pencil me-1"></i>Editar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            {{ $users->links() }}
    </div>
    @endif
    </div>
    @push('scripts')
        <script>
            // Auto-submit do formulário quando os selects mudarem
            document.getElementById('role').addEventListener('change', function() {
                this.form.submit();
            });
            document.getElementById('status').addEventListener('change', function() {
                this.form.submit();
            });
            // Submit com Enter na busca
            document.getElementById('search').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.form.submit();
                }
            });
        </script>
    @endpush
@endsection
