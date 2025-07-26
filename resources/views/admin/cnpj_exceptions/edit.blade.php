@extends('layouts.auth')

@section('title', 'Editar Exceção de CNPJ')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Editar Exceção de CNPJ</h2>
            <a href="{{ route('admin.cnpj-exceptions.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form class="needs-validation" action="{{ route('admin.cnpj-exceptions.update', $cnpjException->id) }}"
                    method="POST" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('cnpj_matriz') is-invalid @enderror"
                                    id="cnpj_matriz" name="cnpj_matriz"
                                    value="{{ old('cnpj_matriz', \App\Utils\Formatter::formatCnpj($cnpjException->cnpj_matriz)) }}"
                                    placeholder="Digite o CNPJ completo da empresa matriz" required>
                                <label for="cnpj_matriz"><i class="bi bi-building me-2"></i>CNPJ da Matriz *</label>
                                @error('cnpj_matriz')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">O campo CNPJ da matriz é obrigatório.</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Razão Social (obtida via API)</label>
                        <input type="text" class="form-control" value="{{ $cnpjException->razao_social }}" disabled
                            readonly>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="bi bi-trash-fill me-2"></i>Excluir Exceção
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Salvar Alterações
                        </button>
                    </div>
                    <!-- Modal de confirmação de exclusão -->
                    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Fechar"></button>
                                </div>
                                <div class="modal-body">
                                    Tem certeza que deseja excluir esta exceção? Esta ação não pode ser desfeita.
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancelar</button>
                                    <form action="{{ route('admin.cnpj-exceptions.destroy', $cnpjException->id) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bi bi-trash-fill me-2"></i>Excluir Exceção
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
