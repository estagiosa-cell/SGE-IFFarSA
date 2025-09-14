@extends('layouts.auth')

@section('title', 'Editar Estágio')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Editar Estágio - {{ $internship->student_name }}</h2>
            <a href="{{ route('admin.internships.index') }}" class="btn btn-primary">
                <i class="bi bi-arrow-left-circle me-2"></i>Voltar
            </a>
        </div>

        <form action="{{ route('admin.internships.update', $internship->id) }}" method="POST" class="needs-validation"
            novalidate>
            @csrf
            @method('PUT')

            {{-- Informações do Sistema --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informações do Sistema</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <select class="form-select @error('status') is-invalid @enderror" id="status"
                                    name="status" required>
                                    @foreach ($statusOptions as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ old('status', $internship->status) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="status">Status do Estágio *</label>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            @if ($internship->google_docs_id)
                                <div class="form-floating">
                                    <div class="form-control d-flex align-items-center">
                                        <a href="https://docs.google.com/document/d/{{ $internship->google_docs_id }}"
                                            target="_blank" class="text-decoration-none">
                                            <i class="bi bi-file-earmark-text me-2"></i>
                                            Abrir no Google Docs
                                            <i class="bi bi-box-arrow-up-right ms-1"></i>
                                        </a>
                                    </div>
                                    <label>Documento do Google</label>
                                </div>
                            @else
                                <div class="form-floating">
                                    <div class="form-control text-muted d-flex align-items-center">
                                        <i class="bi bi-file-earmark-x me-2"></i>
                                        Nenhum documento vinculado
                                    </div>
                                    <label>Documento do Google</label>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-floating">
                                <textarea class="form-control" id="notes" name="notes" style="height: 100px" placeholder="Observações">{{ old('notes', $internship->notes) }}</textarea>
                                <label for="notes">Observações</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dados do Aluno --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-person me-2"></i>Dados do Aluno</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('student_name') is-invalid @enderror"
                                    id="student_name" name="student_name"
                                    value="{{ old('student_name', $internship->student_name) }}" placeholder="Nome completo"
                                    required>
                                <label for="student_name">Nome Completo *</label>
                                @error('student_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="email" class="form-control @error('student_email') is-invalid @enderror"
                                    id="student_email" name="student_email"
                                    value="{{ old('student_email', $internship->student_email) }}"
                                    placeholder="email@exemplo.com" required>
                                <label for="student_email">E-mail *</label>
                                @error('student_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('student_registration_number') is-invalid @enderror"
                                    id="student_registration_number" name="student_registration_number"
                                    value="{{ old('student_registration_number', $internship->student_registration_number) }}"
                                    placeholder="Matrícula" required>
                                <label for="student_registration_number">Matrícula *</label>
                                @error('student_registration_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('student_year_semester') is-invalid @enderror"
                                    id="student_year_semester" name="student_year_semester"
                                    value="{{ old('student_year_semester', $internship->student_year_semester) }}"
                                    placeholder="Ex: 2024/1" required>
                                <label for="student_year_semester">Ano/Semestre *</label>
                                @error('student_year_semester')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="date" class="form-control @error('student_birth_date') is-invalid @enderror"
                                    id="student_birth_date" name="student_birth_date"
                                    value="{{ old('student_birth_date', $internship->student_birth_date?->format('Y-m-d')) }}"
                                    required>
                                <label for="student_birth_date">Data de Nascimento *</label>
                                @error('student_birth_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('student_rg') is-invalid @enderror"
                                    id="student_rg" name="student_rg"
                                    value="{{ old('student_rg', $internship->student_rg) }}" placeholder="RG" required>
                                <label for="student_rg">RG *</label>
                                @error('student_rg')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('student_rg_issuer') is-invalid @enderror"
                                    id="student_rg_issuer" name="student_rg_issuer"
                                    value="{{ old('student_rg_issuer', $internship->student_rg_issuer) }}"
                                    placeholder="Órgão emissor" required>
                                <label for="student_rg_issuer">Órgão Emissor *</label>
                                @error('student_rg_issuer')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="form-floating">
                                <input type="date"
                                    class="form-control @error('student_rg_issue_date') is-invalid @enderror"
                                    id="student_rg_issue_date" name="student_rg_issue_date"
                                    value="{{ old('student_rg_issue_date', $internship->student_rg_issue_date?->format('Y-m-d')) }}"
                                    required>
                                <label for="student_rg_issue_date">Data de Emissão *</label>
                                @error('student_rg_issue_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('student_cpf') is-invalid @enderror"
                                    id="student_cpf" name="student_cpf"
                                    value="{{ old('student_cpf', $internship->student_cpf) }}" placeholder="CPF"
                                    required>
                                <label for="student_cpf">CPF *</label>
                                @error('student_cpf')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('student_phone') is-invalid @enderror"
                                    id="student_phone" name="student_phone"
                                    value="{{ old('student_phone', $internship->student_phone) }}" placeholder="Telefone"
                                    required>
                                <label for="student_phone">Telefone *</label>
                                @error('student_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Endereço do Aluno --}}
                    <h6 class="mb-3 mt-4 border-bottom pb-2">Endereço</h6>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('student_address_street') is-invalid @enderror"
                                    id="student_address_street" name="student_address_street"
                                    value="{{ old('student_address_street', $internship->student_address_street) }}"
                                    placeholder="Rua" required>
                                <label for="student_address_street">Rua *</label>
                                @error('student_address_street')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('student_address_number') is-invalid @enderror"
                                    id="student_address_number" name="student_address_number"
                                    value="{{ old('student_address_number', $internship->student_address_number) }}"
                                    placeholder="Número" required>
                                <label for="student_address_number">Número *</label>
                                @error('student_address_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('student_address_neighborhood') is-invalid @enderror"
                                    id="student_address_neighborhood" name="student_address_neighborhood"
                                    value="{{ old('student_address_neighborhood', $internship->student_address_neighborhood) }}"
                                    placeholder="Bairro" required>
                                <label for="student_address_neighborhood">Bairro *</label>
                                @error('student_address_neighborhood')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('student_address_city') is-invalid @enderror"
                                    id="student_address_city" name="student_address_city"
                                    value="{{ old('student_address_city', $internship->student_address_city) }}"
                                    placeholder="Cidade" required>
                                <label for="student_address_city">Cidade *</label>
                                @error('student_address_city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <select class="form-select @error('student_address_state') is-invalid @enderror"
                                    id="student_address_state" name="student_address_state" required>
                                    <option value="" disabled>Selecione o estado</option>
                                    @foreach (['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'] as $state)
                                        <option value="{{ $state }}"
                                            {{ old('student_address_state', $internship->student_address_state) == $state ? 'selected' : '' }}>
                                            {{ $state }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="student_address_state">Estado *</label>
                                @error('student_address_state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('student_address_zip') is-invalid @enderror"
                                    id="student_address_zip" name="student_address_zip"
                                    value="{{ old('student_address_zip', $internship->student_address_zip) }}"
                                    placeholder="CEP" required>
                                <label for="student_address_zip">CEP *</label>
                                @error('student_address_zip')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dados do Responsável Legal --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-person-check me-2"></i>Dados do Responsável Legal</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('legal_guardian_name') is-invalid @enderror"
                                    id="legal_guardian_name" name="legal_guardian_name"
                                    value="{{ old('legal_guardian_name', $internship->legal_guardian_name) }}"
                                    placeholder="Nome do responsável">
                                <label for="legal_guardian_name">Nome do Responsável Legal</label>
                                @error('legal_guardian_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('legal_guardian_cpf') is-invalid @enderror"
                                    id="legal_guardian_cpf" name="legal_guardian_cpf"
                                    value="{{ old('legal_guardian_cpf', $internship->legal_guardian_cpf) }}"
                                    placeholder="CPF">
                                <label for="legal_guardian_cpf">CPF</label>
                                @error('legal_guardian_cpf')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <select class="form-select @error('legal_guardian_kinship') is-invalid @enderror"
                                    id="legal_guardian_kinship" name="legal_guardian_kinship">
                                    <option value="">Selecione o parentesco</option>
                                    <option value="Pai"
                                        {{ old('legal_guardian_kinship', $internship->legal_guardian_kinship) == 'Pai' ? 'selected' : '' }}>
                                        Pai</option>
                                    <option value="Mãe"
                                        {{ old('legal_guardian_kinship', $internship->legal_guardian_kinship) == 'Mãe' ? 'selected' : '' }}>
                                        Mãe</option>
                                    <option value="Outro"
                                        {{ old('legal_guardian_kinship', $internship->legal_guardian_kinship) == 'Outro' ? 'selected' : '' }}>
                                        Outro</option>
                                </select>
                                <label for="legal_guardian_kinship">Parentesco</label>
                                @error('legal_guardian_kinship')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="email"
                                    class="form-control @error('legal_guardian_email') is-invalid @enderror"
                                    id="legal_guardian_email" name="legal_guardian_email"
                                    value="{{ old('legal_guardian_email', $internship->legal_guardian_email) }}"
                                    placeholder="E-mail">
                                <label for="legal_guardian_email">E-mail</label>
                                @error('legal_guardian_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dados da Empresa/Parte Concedente --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-building me-2"></i>Dados da Parte Concedente</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('company_legal_identifier') is-invalid @enderror"
                                    id="company_legal_identifier" name="company_legal_identifier"
                                    value="{{ old('company_legal_identifier', $internship->company_legal_identifier) }}"
                                    placeholder="CNPJ/CPF" required>
                                <label for="company_legal_identifier">CNPJ/CPF *</label>
                                @error('company_legal_identifier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex gap-2">
                                <div class="flex-grow-1">
                                    @if (count($companiesWithSameCnpj) > 1)
                                        <div class="form-floating">
                                            <select class="form-select" id="company_select" name="company_id">
                                                <option value="">Selecione uma empresa</option>
                                                @foreach ($companiesWithSameCnpj as $company)
                                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                                @endforeach
                                            </select>
                                            <label for="company_select">Empresas com mesmo CNPJ</label>
                                        </div>
                                    @else
                                        <div class="form-floating">
                                            <input type="text" class="form-control" value="Nenhuma empresa encontrada"
                                                readonly>
                                            <label>Empresas cadastradas</label>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-outline-primary" id="updateCompaniesBtn">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                                    id="company_name" name="company_name"
                                    value="{{ old('company_name', $internship->company_name) }}"
                                    placeholder="Nome da empresa" required>
                                <label for="company_name">Nome da Empresa *</label>
                                @error('company_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('company_phone') is-invalid @enderror"
                                    id="company_phone" name="company_phone"
                                    value="{{ old('company_phone', $internship->company_phone) }}"
                                    placeholder="Telefone">
                                <label for="company_phone">Telefone</label>
                                @error('company_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="email" class="form-control @error('company_email') is-invalid @enderror"
                                    id="company_email" name="company_email"
                                    value="{{ old('company_email', $internship->company_email) }}" placeholder="E-mail">
                                <label for="company_email">E-mail</label>
                                @error('company_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('company_representative_name') is-invalid @enderror"
                                    id="company_representative_name" name="company_representative_name"
                                    value="{{ old('company_representative_name', $internship->company_representative_name) }}"
                                    placeholder="Nome do representante" required>
                                <label for="company_representative_name">Nome do Representante *</label>
                                @error('company_representative_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('company_representative_role') is-invalid @enderror"
                                    id="company_representative_role" name="company_representative_role"
                                    value="{{ old('company_representative_role', $internship->company_representative_role) }}"
                                    placeholder="Cargo do representante" required>
                                <label for="company_representative_role">Cargo do Representante *</label>
                                @error('company_representative_role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('field_of_activity') is-invalid @enderror"
                                    id="field_of_activity" name="field_of_activity"
                                    value="{{ old('field_of_activity', $internship->field_of_activity) }}"
                                    placeholder="Área de atuação" required>
                                <label for="field_of_activity">Área de Atuação *</label>
                                @error('field_of_activity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Endereço da Empresa --}}
                    <h6 class="mb-3 mt-4 border-bottom pb-2">Endereço da Empresa</h6>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('company_address_street') is-invalid @enderror"
                                    id="company_address_street" name="company_address_street"
                                    value="{{ old('company_address_street', $internship->company_address_street) }}"
                                    placeholder="Rua">
                                <label for="company_address_street">Rua</label>
                                @error('company_address_street')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('company_address_number') is-invalid @enderror"
                                    id="company_address_number" name="company_address_number"
                                    value="{{ old('company_address_number', $internship->company_address_number) }}"
                                    placeholder="Número">
                                <label for="company_address_number">Número</label>
                                @error('company_address_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('company_address_neighborhood') is-invalid @enderror"
                                    id="company_address_neighborhood" name="company_address_neighborhood"
                                    value="{{ old('company_address_neighborhood', $internship->company_address_neighborhood) }}"
                                    placeholder="Bairro">
                                <label for="company_address_neighborhood">Bairro</label>
                                @error('company_address_neighborhood')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('company_address_city') is-invalid @enderror"
                                    id="company_address_city" name="company_address_city"
                                    value="{{ old('company_address_city', $internship->company_address_city) }}"
                                    placeholder="Cidade">
                                <label for="company_address_city">Cidade</label>
                                @error('company_address_city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <select class="form-select @error('company_address_state') is-invalid @enderror"
                                    id="company_address_state" name="company_address_state">
                                    <option value="" disabled selected>Selecione o estado</option>
                                    @foreach (['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'] as $state)
                                        <option value="{{ $state }}"
                                            {{ old('company_address_state', $internship->company_address_state) == $state ? 'selected' : '' }}>
                                            {{ $state }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="company_address_state">Estado</label>
                                @error('company_address_state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('company_address_zip') is-invalid @enderror"
                                    id="company_address_zip" name="company_address_zip"
                                    value="{{ old('company_address_zip', $internship->company_address_zip) }}"
                                    placeholder="CEP">
                                <label for="company_address_zip">CEP</label>
                                @error('company_address_zip')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dados do Supervisor --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Dados do Supervisor</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('supervisor_name') is-invalid @enderror"
                                    id="supervisor_name" name="supervisor_name"
                                    value="{{ old('supervisor_name', $internship->supervisor_name) }}"
                                    placeholder="Nome do supervisor" required>
                                <label for="supervisor_name">Nome do Supervisor *</label>
                                @error('supervisor_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('supervisor_phone') is-invalid @enderror"
                                    id="supervisor_phone" name="supervisor_phone"
                                    value="{{ old('supervisor_phone', $internship->supervisor_phone) }}"
                                    placeholder="Telefone">
                                <label for="supervisor_phone">Telefone</label>
                                @error('supervisor_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="email"
                                    class="form-control @error('supervisor_email') is-invalid @enderror"
                                    id="supervisor_email" name="supervisor_email"
                                    value="{{ old('supervisor_email', $internship->supervisor_email) }}"
                                    placeholder="E-mail">
                                <label for="supervisor_email">E-mail</label>
                                @error('supervisor_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('supervisor_role') is-invalid @enderror"
                                    id="supervisor_role" name="supervisor_role"
                                    value="{{ old('supervisor_role', $internship->supervisor_role) }}"
                                    placeholder="Cargo" required>
                                <label for="supervisor_role">Cargo *</label>
                                @error('supervisor_role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dados do Estágio --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-briefcase me-2"></i>Dados do Estágio</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('internship_type_name') is-invalid @enderror"
                                    id="internship_type_name" name="internship_type_name"
                                    value="{{ old('internship_type_name', $internship->internship_type_name) }}"
                                    placeholder="Tipo de estágio" required>
                                <label for="internship_type_name">Tipo de Estágio *</label>
                                @error('internship_type_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('internship_sector') is-invalid @enderror"
                                    id="internship_sector" name="internship_sector"
                                    value="{{ old('internship_sector', $internship->internship_sector) }}"
                                    placeholder="Setor">
                                <label for="internship_sector">Setor</label>
                                @error('internship_sector')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                    id="start_date" name="start_date"
                                    value="{{ old('start_date', $internship->start_date?->format('Y-m-d')) }}" required>
                                <label for="start_date">Data de Início *</label>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                    id="end_date" name="end_date"
                                    value="{{ old('end_date', $internship->end_date?->format('Y-m-d')) }}" required>
                                <label for="end_date">Data de Término *</label>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="number" class="form-control @error('required_hours') is-invalid @enderror"
                                    id="required_hours" name="required_hours"
                                    value="{{ old('required_hours', $internship->required_hours) }}"
                                    placeholder="Horas obrigatórias" required>
                                <label for="required_hours">Horas Obrigatórias *</label>
                                @error('required_hours')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="form-floating">
                                <input type="number"
                                    class="form-control @error('evaluation_grade') is-invalid @enderror"
                                    id="evaluation_grade" name="evaluation_grade"
                                    value="{{ old('evaluation_grade', $internship->evaluation_grade) }}"
                                    placeholder="Nota" min="0" max="100" step="0.1">
                                <label for="evaluation_grade">Nota da Avaliação (0 - 100)</label>
                                @error('evaluation_grade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-9 mb-3">
                            <div class="form-floating">
                                <textarea class="form-control @error('activities') is-invalid @enderror" id="activities" name="activities"
                                    style="height: 120px" placeholder="Descrição das atividades" required>{{ old('activities', $internship->activities) }}</textarea>
                                <label for="activities">Atividades *</label>
                                @error('activities')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Botões de Ação --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.internships.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Salvar Alterações
                        </button>
                    </div>
                </div>
            </div>
        </form>

        {{-- JavaScript para funcionalidade AJAX - Dentro da div principal --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const updateBtn = document.getElementById('updateCompaniesBtn');
                const cnpjInput = document.getElementById('company_legal_identifier');
                const companySelect = document.getElementById('company_select');

                // Função para atualizar empresas
                function updateCompanies() {
                    const cnpj = cnpjInput.value.trim();

                    if (!cnpj) {
                        alert('Por favor, informe o CNPJ/CPF primeiro.');
                        return;
                    }

                    fetch(`{{ route('admin.internships.companies-by-cnpj') }}?cnpj=${cnpj}`)
                        .then(response => response.json())
                        .then(companies => {
                            if (companySelect) {
                                companySelect.innerHTML = '<option value="">Selecione uma empresa</option>';

                                companies.forEach(company => {
                                    const option = document.createElement('option');
                                    option.value = company.id;
                                    option.textContent = company.name;
                                    companySelect.appendChild(option);
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Erro ao buscar empresas:', error);
                            alert('Erro ao buscar empresas. Tente novamente.');
                        });
                }

                // Evento do botão de atualizar
                if (updateBtn) {
                    updateBtn.addEventListener('click', updateCompanies);
                }

                // Evento de mudança no select de empresas
                if (companySelect) {
                    companySelect.addEventListener('change', function() {
                        const companyId = this.value;

                        if (companyId) {
                            fetch(
                                    `{{ route('admin.internships.companies-by-cnpj') }}?cnpj=${cnpjInput.value}`
                                    )
                                .then(response => response.json())
                                .then(companies => {
                                    const selectedCompany = companies.find(c => c.id == companyId);

                                    if (selectedCompany) {
                                        // Preenche os campos com os dados da empresa selecionada
                                        document.getElementById('company_name').value = selectedCompany
                                            .name || '';
                                        document.getElementById('company_representative_name').value =
                                            selectedCompany.representative_name || '';
                                        document.getElementById('company_representative_role').value =
                                            selectedCompany.representative_role || '';
                                        document.getElementById('company_phone').value = selectedCompany
                                            .phone || '';
                                        document.getElementById('company_email').value = selectedCompany
                                            .email || '';
                                        document.getElementById('field_of_activity').value = selectedCompany
                                            .field_of_activity || '';

                                        // Preenche os campos de endereço da empresa
                                        document.getElementById('company_address_street').value =
                                            selectedCompany
                                            .address_street || '';
                                        document.getElementById('company_address_number').value =
                                            selectedCompany
                                            .address_number || '';
                                        document.getElementById('company_address_neighborhood').value =
                                            selectedCompany
                                            .address_neighborhood || '';
                                        document.getElementById('company_address_city').value =
                                            selectedCompany
                                            .address_city || '';
                                        document.getElementById('company_address_state').value =
                                            selectedCompany
                                            .address_state || '';
                                        document.getElementById('company_address_zip').value =
                                            selectedCompany
                                            .address_zip || '';
                                    }
                                });
                        }
                    });
                }
            });
        </script>
    </div>
@endsection
