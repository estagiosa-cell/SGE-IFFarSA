@extends('layouts.auth')

@section('title', 'Editar Credenciamento')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Editar Credenciamento - {{ $accreditation->name }}</h2>
            <a href="{{ route('admin.accreditations.index') }}" class="btn btn-primary">
                <i class="bi bi-arrow-left-circle me-2"></i>Voltar
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form class="needs-validation" action="{{ route('admin.accreditations.update', $accreditation->id) }}"
                    method="POST" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- Nome -->
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', $accreditation->name) }}"
                                    placeholder="Ex: João da Silva" required>
                                <label for="name"><i class="bi bi-person me-2"></i>Nome Completo *</label>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">O campo nome é obrigatório.</div>
                                @enderror
                            </div>
                        </div>

                        <!-- CPF -->
                        <div class="col-md-3 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('cpf') is-invalid @enderror" id="cpf"
                                    name="cpf"
                                    value="{{ old('cpf', \App\Utils\Formatter::formatCpf($accreditation->cpf)) }}"
                                    placeholder="000.000.000-00" maxlength="14" required>
                                <label for="cpf"><i class="bi bi-person-vcard me-2"></i>CPF *</label>
                                @error('cpf')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">O campo CPF é obrigatório.</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Número do Processo -->
                        <div class="col-md-3 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('process_number') is-invalid @enderror"
                                    id="process_number" name="process_number"
                                    value="{{ old('process_number', $accreditation->process_number) }}"
                                    placeholder="Ex: 2024001" required>
                                <label for="process_number"><i class="bi bi-file-text me-2"></i>Nº do Processo *</label>
                                @error('process_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">O campo número do processo é obrigatório.</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Informações adicionais -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body py-2">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar-plus me-1"></i>
                                        <strong>Criado em:</strong> {{ $accreditation->created_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body py-2">
                                    <small class="text-muted">
                                        <i class="bi bi-pencil-square me-1"></i>
                                        <strong>Atualizado em:</strong>
                                        {{ $accreditation->updated_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Área de Informação -->
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Informações importantes:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Todos os campos marcados com (*) são obrigatórios</li>
                            <li>Digite o CPF apenas com números, a formatação será aplicada automaticamente</li>
                            <li>O número do processo deve ser único para cada credenciamento</li>
                        </ul>
                    </div>

                    <!-- Botões -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.accreditations.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Máscara para CPF
            document.getElementById('cpf').addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                e.target.value = value;
            });

            // Validação de CPF simples
            function validarCPF(cpf) {
                cpf = cpf.replace(/[^\d]+/g, '');
                if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) return false;

                let soma = 0;
                for (let i = 0; i < 9; i++) {
                    soma += parseInt(cpf.charAt(i)) * (10 - i);
                }
                let resto = (soma * 10) % 11;
                if (resto === 10 || resto === 11) resto = 0;
                if (resto !== parseInt(cpf.charAt(9))) return false;

                soma = 0;
                for (let i = 0; i < 10; i++) {
                    soma += parseInt(cpf.charAt(i)) * (11 - i);
                }
                resto = (soma * 10) % 11;
                if (resto === 10 || resto === 11) resto = 0;
                return resto === parseInt(cpf.charAt(10));
            }

            // Validação do formulário
            document.querySelector('form').addEventListener('submit', function(e) {
                const cpfField = document.getElementById('cpf');
                const cpf = cpfField.value;

                if (!validarCPF(cpf)) {
                    e.preventDefault();
                    cpfField.classList.add('is-invalid');
                    const feedback = cpfField.nextElementSibling.nextElementSibling;
                    if (feedback) {
                        feedback.textContent = 'CPF inválido.';
                    }
                } else {
                    cpfField.classList.remove('is-invalid');
                }
            });

            // Aplicar máscara no carregamento da página se já houver valor
            document.addEventListener('DOMContentLoaded', function() {
                const cpfField = document.getElementById('cpf');
                if (cpfField.value) {
                    cpfField.dispatchEvent(new Event('input'));
                }
            });
        </script>
    @endpush
@endsection
