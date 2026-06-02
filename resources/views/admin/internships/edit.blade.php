@extends('layouts.auth')

@section('title', 'Editar Estágio')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Editar Estágio - {{ $internship->student_name }}</h2>
            <a href="{{ route('admin.internships.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left-circle me-2"></i>Voltar
            </a>
        </div>

        {{-- Geração de Documentos --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white py-2">
                <h5 class="mb-0">
                    <i class="bi bi-file-earmark-text me-2"></i>Geração de Documentos
                </h5>
            </div>
            <div class="card-body">
                <form id="generateDocForm" action="{{ route('admin.internships.documents.generate', $internship->id) }}"
                    method="POST" class="needs-validation" novalidate>
                    @csrf

                    <div class="row m-0 g-3">
                        <div class="col-md-8">
                            <div class="form-floating">
                                <select class="form-select" id="document_type" name="document_type" required>
                                    <option value="" disabled selected>Selecione o tipo de documento</option>
                                    <option value="termo-compromisso">Termo de Compromisso Padrão</option>
                                    <option value="termo-emater-rs">Termo de Compromisso EMATER/RS</option>
                                    <option value="termo-seduc">Termo de Compromisso SEDUC</option>
                                    <option value="rescisao">Termo de Rescisão de Estágio</option>
                                    <option value="credenciamento">Termo de Credenciamento</option>
                                </select>
                                <label for="document_type">
                                    <i class="bi bi-file-earmark-text me-2"></i>Tipo de Documento *
                                </label>
                                <div class="invalid-feedback">Por favor, selecione o tipo de documento</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" id="generateDocBtn" class="btn btn-primary w-100 h-100">
                                <i class="bi bi-file-earmark-plus me-2"></i>
                                Gerar Documento
                            </button>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <small>
                                <strong>Informação:</strong> O link será atualizado no sistema, mas o documento permanece
                                salvo no Google Drive.
                            </small>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <form action="{{ route('admin.internships.update', $internship->id) }}" method="POST" class="needs-validation"
            novalidate>
            @csrf
            @method('PUT')

            <div class="accordion mb-4" id="internshipAccordion">
                {{-- Informações do Sistema --}}
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingSystem">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseSystem" aria-expanded="true" aria-controls="collapseSystem">
                            <i class="bi bi-info-circle me-2"></i>Informações do Sistema
                        </button>
                    </h2>
                    <div id="collapseSystem" class="accordion-collapse collapse show" aria-labelledby="headingSystem">
                        <div class="accordion-body">
                            <div class="row m-0 m-0">
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <select class="form-select @error('advisor_id') is-invalid @enderror"
                                            id="advisor_id" name="advisor_id" required>
                                            <option value="" disabled>Selecione o orientador</option>
                                            @foreach ($advisors as $advisor)
                                                <option value="{{ $advisor->id }}"
                                                    {{ old('advisor_id', $internship->advisor_id) == $advisor->id ? 'selected' : '' }}>
                                                    {{ $advisor->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="advisor_id">Orientador *</label>
                                        @error('advisor_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <select class="form-select @error('status') is-invalid @enderror" id="status"
                                            name="status" required>
                                            @foreach ($statusOptions as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ old('status', $internship->status?->value) == $value ? 'selected' : '' }}>
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
                            </div>
                            <div class="row m-0 m-0">
                            </div>
                            <div class="row m-0 m-0">
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
                            <div class="row m-0 m-0">
                                <div class="col-md-12 mb-3">
                                    <div class="form-floating">
                                        <textarea class="form-control" id="notes" name="notes" style="height: 200px" placeholder="Observações">{{ old('notes', $internship->notes) }}</textarea>
                                        <label for="notes">Observações</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dados do Aluno --}}
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingStudent">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseStudent" aria-expanded="false" aria-controls="collapseStudent">
                            <i class="bi bi-person me-2"></i>Dados do Aluno
                        </button>
                    </h2>
                    <div id="collapseStudent" class="accordion-collapse collapse" aria-labelledby="headingStudent">
                        <div class="accordion-body">
                            <div class="row m-0 m-0">
                                <div class="col-md-8 mb-3">
                                    <div class="form-floating">
                                        <input type="text"
                                            class="form-control @error('student_name') is-invalid @enderror"
                                            id="student_name" name="student_name"
                                            value="{{ old('student_name', $internship->student_name) }}"
                                            placeholder="Nome completo" required>
                                        <label for="student_name">Nome Completo *</label>
                                        @error('student_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-floating">
                                        <input type="email"
                                            class="form-control @error('student_email') is-invalid @enderror"
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
                            <div class="row m-0 m-0">
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
                                        <input type="date"
                                            class="form-control @error('student_birth_date') is-invalid @enderror"
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
                            <div class="row m-0 m-0">
                                <div class="col-md-3 mb-3">
                                    <div class="form-floating">
                                        <input type="text"
                                            class="form-control @error('student_rg') is-invalid @enderror" id="student_rg"
                                            name="student_rg" value="{{ old('student_rg', $internship->student_rg) }}"
                                            placeholder="RG" required>
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
                                        <input type="text"
                                            class="form-control @error('student_cpf') is-invalid @enderror"
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
                            <div class="row m-0 m-0">
                                <div class="col-md-4 mb-3">
                                    <div class="form-floating">
                                        <input type="text"
                                            class="form-control @error('student_phone') is-invalid @enderror"
                                            id="student_phone" name="student_phone"
                                            value="{{ old('student_phone', $internship->student_phone) }}"
                                            placeholder="Telefone" required>
                                        <label for="student_phone">Telefone *</label>
                                        @error('student_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Endereço do Aluno --}}
                            <h6 class="mb-3 mt-4 border-bottom pb-2">Endereço</h6>
                            <div class="row m-0 m-0">
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
                            <div class="row m-0 m-0">
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
                            <div class="row m-0 m-0">
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
                </div>

                {{-- Dados do Responsável Legal --}}
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingLegalGuardian">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseLegalGuardian" aria-expanded="false"
                            aria-controls="collapseLegalGuardian">
                            <i class="bi bi-person-check me-2"></i>Dados do Responsável Legal
                        </button>
                    </h2>
                    <div id="collapseLegalGuardian" class="accordion-collapse collapse"
                        aria-labelledby="headingLegalGuardian">
                        <div class="accordion-body">
                            <div class="row m-0 m-0">
                                <div class="col-md-4 mb-3">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="student_is_adult" value="0">
                                        <input class="form-check-input" type="checkbox" id="student_is_adult"
                                            name="student_is_adult" value="1"
                                            {{ old('student_is_adult', $internship->student_is_adult) == '1' || old('student_is_adult', $internship->student_is_adult) === true ? 'checked' : '' }}
                                            onchange="toggleLegalGuardianFields()">
                                        <label class="form-check-label" for="student_is_adult">
                                            <strong>Aluno Maior de Idade</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row m-0" id="legal-guardian-fields">
                                <div class="col-md-8 mb-3">
                                    <div class="form-floating">
                                        <input type="text"
                                            class="form-control @error('legal_guardian_name') is-invalid @enderror"
                                            id="legal_guardian_name" name="legal_guardian_name"
                                            value="{{ old('legal_guardian_name', $internship->legal_guardian_name) }}"
                                            placeholder="Nome do responsável"
                                            {{ old('student_is_adult', $internship->student_is_adult) == '1' || old('student_is_adult', $internship->student_is_adult) === true ? 'readonly' : '' }}>
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
                                            placeholder="CPF"
                                            {{ old('student_is_adult', $internship->student_is_adult) == '1' || old('student_is_adult', $internship->student_is_adult) === true ? 'readonly' : '' }}>
                                        <label for="legal_guardian_cpf">CPF</label>
                                        @error('legal_guardian_cpf')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row m-0 m-0">
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <select class="form-select @error('legal_guardian_kinship') is-invalid @enderror"
                                            id="legal_guardian_kinship" name="legal_guardian_kinship"
                                            style="{{ old('student_is_adult', $internship->student_is_adult) == '1' || old('student_is_adult', $internship->student_is_adult) === true ? 'display: none;' : '' }}">
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
                                        <input type="text" class="form-control" id="legal_guardian_kinship_readonly"
                                            readonly value="" placeholder="Não aplicável (maior de idade)"
                                            style="{{ old('student_is_adult', $internship->student_is_adult) == '1' || old('student_is_adult', $internship->student_is_adult) === true ? '' : 'display: none;' }}">
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
                                            placeholder="E-mail"
                                            {{ old('student_is_adult', $internship->student_is_adult) == '1' || old('student_is_adult', $internship->student_is_adult) === true ? 'readonly' : '' }}>
                                        <label for="legal_guardian_email">E-mail</label>
                                        @error('legal_guardian_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dados da Empresa/Parte Concedente --}}
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingCompany">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseCompany" aria-expanded="false" aria-controls="collapseCompany">
                            <i class="bi bi-building me-2"></i>Dados da Parte Concedente
                        </button>
                    </h2>
                    <div id="collapseCompany" class="accordion-collapse collapse" aria-labelledby="headingCompany">
                        <div class="accordion-body">
                            <div class="row m-0 m-0">
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
                                        <div class="flex-grow-1" id="company_select_container">
                                            <div class="form-floating">
                                                <input type="text" class="form-control"
                                                    value="Clique no botão ao lado para buscar" readonly>
                                                <label>Empresas cadastradas</label>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary"
                                            onclick="buscarDadosConcedente()">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row m-0 m-0">
                                <div class="col-md-12 mb-3">
                                    <div class="form-floating">
                                        <input type="text"
                                            class="form-control @error('company_name') is-invalid @enderror"
                                            id="company_name" name="company_name"
                                            value="{{ old('company_name', $internship->company_name) }}"
                                            placeholder="Nome da empresa">
                                        <label for="company_name">Nome da Empresa *</label>
                                        @error('company_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row m-0 m-0">
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input type="text"
                                            class="form-control @error('company_phone') is-invalid @enderror"
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
                                        <input type="email"
                                            class="form-control @error('company_email') is-invalid @enderror"
                                            id="company_email" name="company_email"
                                            value="{{ old('company_email', $internship->company_email) }}"
                                            placeholder="E-mail">
                                        <label for="company_email">E-mail</label>
                                        @error('company_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row m-0 m-0">
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input type="text"
                                            class="form-control @error('company_representative_name') is-invalid @enderror"
                                            id="company_representative_name" name="company_representative_name"
                                            value="{{ old('company_representative_name', $internship->company_representative_name) }}"
                                            placeholder="Nome do representante">
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
                                            placeholder="Cargo do representante">
                                        <label for="company_representative_role">Cargo do Representante *</label>
                                        @error('company_representative_role')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row m-0 m-0">
                                <div class="col-md-12 mb-3">
                                    <div class="form-floating">
                                        <input type="text"
                                            class="form-control @error('field_of_activity') is-invalid @enderror"
                                            id="field_of_activity" name="field_of_activity"
                                            value="{{ old('field_of_activity', $internship->field_of_activity) }}"
                                            placeholder="Área de atuação">
                                        <label for="field_of_activity">Área de Atuação *</label>
                                        @error('field_of_activity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Endereço da Empresa --}}
                            <h6 class="mb-3 mt-4 border-bottom pb-2">Endereço da Empresa</h6>
                            <div class="row m-0 m-0">
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
                            <div class="row m-0 m-0">
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
                            <div class="row m-0 m-0">
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

                            {{-- Informações Adicionais da Empresa --}}
                            <h6 class="mb-3 mt-4 border-bottom pb-2">Informações Adicionais</h6>
                            <div class="row m-0 m-0">
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input type="text"
                                            class="form-control @error('professional_council') is-invalid @enderror"
                                            id="professional_council" name="professional_council"
                                            value="{{ old('professional_council', $internship->professional_council) }}"
                                            placeholder="Ex: CREA-RS">
                                        <label for="professional_council">Conselho Profissional</label>
                                        @error('professional_council')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input type="text"
                                            class="form-control @error('council_registration_number') is-invalid @enderror"
                                            id="council_registration_number" name="council_registration_number"
                                            value="{{ old('council_registration_number', $internship->council_registration_number) }}"
                                            placeholder="Ex: 123456">
                                        <label for="council_registration_number">Nº de Registro no Conselho</label>
                                        @error('council_registration_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row m-0 m-0">
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input type="text"
                                            class="form-control @error('process_number') is-invalid @enderror"
                                            id="process_number" name="process_number"
                                            value="{{ old('process_number', $internship->process_number) }}"
                                            placeholder="Ex: 23451.000123/2024-01">
                                        <label for="process_number">Nº do Processo / Credenciamento</label>
                                        @error('process_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-text mt-1">
                                        <small>Obrigatório para gerar documentos de credenciamento.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dados do Supervisor --}}
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingSupervisor">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseSupervisor" aria-expanded="false"
                            aria-controls="collapseSupervisor">
                            <i class="bi bi-person-badge me-2"></i>Dados do Supervisor
                        </button>
                    </h2>
                    <div id="collapseSupervisor" class="accordion-collapse collapse" aria-labelledby="headingSupervisor">
                        <div class="accordion-body">
                            <div class="row m-0 m-0">
                                <div class="col-md-8 mb-3">
                                    <div class="form-floating">
                                        <input type="text"
                                            class="form-control @error('supervisor_name') is-invalid @enderror"
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
                            <div class="row m-0 m-0">
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
                                        <input type="text"
                                            class="form-control @error('supervisor_role') is-invalid @enderror"
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
                </div>

                {{-- Dados do Estágio --}}
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingInternship">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseInternship" aria-expanded="false"
                            aria-controls="collapseInternship">
                            <i class="bi bi-briefcase me-2"></i>Dados do Estágio
                        </button>
                    </h2>
                    <div id="collapseInternship" class="accordion-collapse collapse" aria-labelledby="headingInternship">
                        <div class="accordion-body">
                            <div class="row m-0 m-0">
                                <div class="col-md-5 mb-3">
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
                                <div class="col-md-2 mb-3">
                                    <div class="form-floating">
                                        <input type="number"
                                            class="form-control @error('internship_type_weight') is-invalid @enderror"
                                            id="internship_type_weight" name="internship_type_weight" min="1"
                                            max="10" step="1"
                                            value="{{ old('internship_type_weight', $internship->internship_type_weight ?? 1) }}">
                                        <label for="internship_type_weight">Peso</label>
                                        @error('internship_type_weight')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted">Escala: 1-10</small>
                                </div>
                                <div class="col-md-5 mb-3">
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

                            {{-- Valores dos Conceitos --}}
                            <h6 class="mb-3 mt-3">Valores dos Conceitos</h6>
                            <div class="row m-0 m-0">
                                <div class="col-md-2 mb-3">
                                    <div class="form-floating">
                                        <input type="number" step="0.01" min="0"
                                            class="form-control @error('great_value') is-invalid @enderror"
                                            id="great_value" name="great_value"
                                            value="{{ old('great_value', $internship->great_value ?? '') }}" required>
                                        <label for="great_value">Ótimo *</label>
                                        @error('great_value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <div class="form-floating">
                                        <input type="number" step="0.01" min="0"
                                            class="form-control @error('very_good_value') is-invalid @enderror"
                                            id="very_good_value" name="very_good_value"
                                            value="{{ old('very_good_value', $internship->very_good_value ?? '') }}"
                                            required>
                                        <label for="very_good_value">Muito Bom *</label>
                                        @error('very_good_value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <div class="form-floating">
                                        <input type="number" step="0.01" min="0"
                                            class="form-control @error('good_value') is-invalid @enderror"
                                            id="good_value" name="good_value"
                                            value="{{ old('good_value', $internship->good_value ?? '') }}" required>
                                        <label for="good_value">Bom *</label>
                                        @error('good_value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <div class="form-floating">
                                        <input type="number" step="0.01" min="0"
                                            class="form-control @error('satisfactory_value') is-invalid @enderror"
                                            id="satisfactory_value" name="satisfactory_value"
                                            value="{{ old('satisfactory_value', $internship->satisfactory_value ?? '') }}"
                                            required>
                                        <label for="satisfactory_value">Satisfatório *</label>
                                        @error('satisfactory_value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <div class="form-floating">
                                        <input type="number" step="0.01" min="0"
                                            class="form-control @error('unsatisfactory_value') is-invalid @enderror"
                                            id="unsatisfactory_value" name="unsatisfactory_value"
                                            value="{{ old('unsatisfactory_value', $internship->unsatisfactory_value ?? '') }}"
                                            required>
                                        <label for="unsatisfactory_value">Insatisfatório *</label>
                                        @error('unsatisfactory_value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row m-0 m-0">
                                <div class="col-md-4 mb-3">
                                    <div class="form-floating">
                                        <input type="date"
                                            class="form-control @error('start_date') is-invalid @enderror"
                                            id="start_date" name="start_date"
                                            value="{{ old('start_date', $internship->start_date?->format('Y-m-d')) }}"
                                            required>
                                        <label for="start_date">Data de Início *</label>
                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-floating">
                                        <input type="date"
                                            class="form-control @error('end_date') is-invalid @enderror" id="end_date"
                                            name="end_date"
                                            value="{{ old('end_date', $internship->end_date?->format('Y-m-d')) }}"
                                            required>
                                        <label for="end_date">Data de Término *</label>
                                        @error('end_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-floating">
                                        <input type="number"
                                            class="form-control @error('required_hours') is-invalid @enderror"
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
                            <div class="row m-0 m-0">
                                <div class="col-md-12 mb-3">
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#recalculateEndDateModal">
                                        <i class="bi bi-calculator me-2"></i>Recalcular Data de Término
                                    </button>
                                </div>
                            </div>

                            {{-- Carga Horária Semanal --}}
                            <h6 class="mb-3 mt-4 border-bottom pb-2">Carga Horária Semanal</h6>
                            <div class="row m-0 m-0">
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="form-floating">
                                        <input type="number" step="1" min="0" max="6"
                                            class="form-control @error('hours_sunday') is-invalid @enderror"
                                            id="hours_sunday" name="hours_sunday"
                                            value="{{ old('hours_sunday', $internship->hours_sunday ?? '') }}"
                                            placeholder="0">
                                        <label for="hours_sunday">Domingo</label>
                                        @error('hours_sunday')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="form-floating">
                                        <input type="number" step="1" min="0" max="6"
                                            class="form-control @error('hours_monday') is-invalid @enderror"
                                            id="hours_monday" name="hours_monday"
                                            value="{{ old('hours_monday', $internship->hours_monday ?? '') }}"
                                            placeholder="0">
                                        <label for="hours_monday">Segunda</label>
                                        @error('hours_monday')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="form-floating">
                                        <input type="number" step="1" min="0" max="6"
                                            class="form-control @error('hours_tuesday') is-invalid @enderror"
                                            id="hours_tuesday" name="hours_tuesday"
                                            value="{{ old('hours_tuesday', $internship->hours_tuesday ?? '') }}"
                                            placeholder="0">
                                        <label for="hours_tuesday">Terça</label>
                                        @error('hours_tuesday')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="form-floating">
                                        <input type="number" step="1" min="0" max="6"
                                            class="form-control @error('hours_wednesday') is-invalid @enderror"
                                            id="hours_wednesday" name="hours_wednesday"
                                            value="{{ old('hours_wednesday', $internship->hours_wednesday ?? '') }}"
                                            placeholder="0">
                                        <label for="hours_wednesday">Quarta</label>
                                        @error('hours_wednesday')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row m-0 m-0">
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="form-floating">
                                        <input type="number" step="1" min="0" max="6"
                                            class="form-control @error('hours_thursday') is-invalid @enderror"
                                            id="hours_thursday" name="hours_thursday"
                                            value="{{ old('hours_thursday', $internship->hours_thursday ?? '') }}"
                                            placeholder="0">
                                        <label for="hours_thursday">Quinta</label>
                                        @error('hours_thursday')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="form-floating">
                                        <input type="number" step="1" min="0" max="6"
                                            class="form-control @error('hours_friday') is-invalid @enderror"
                                            id="hours_friday" name="hours_friday"
                                            value="{{ old('hours_friday', $internship->hours_friday ?? '') }}"
                                            placeholder="0">
                                        <label for="hours_friday">Sexta</label>
                                        @error('hours_friday')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="form-floating">
                                        <input type="number" step="1" min="0" max="6"
                                            class="form-control @error('hours_saturday') is-invalid @enderror"
                                            id="hours_saturday" name="hours_saturday"
                                            value="{{ old('hours_saturday', $internship->hours_saturday ?? '') }}"
                                            placeholder="0">
                                        <label for="hours_saturday">Sábado</label>
                                        @error('hours_saturday')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="form-floating">
                                        <input type="text" class="form-control bg-light" id="total_weekly_hours"
                                            value="{{ old('hours_sunday', $internship->hours_sunday ?? 0) + old('hours_monday', $internship->hours_monday ?? 0) + old('hours_tuesday', $internship->hours_tuesday ?? 0) + old('hours_wednesday', $internship->hours_wednesday ?? 0) + old('hours_thursday', $internship->hours_thursday ?? 0) + old('hours_friday', $internship->hours_friday ?? 0) + old('hours_saturday', $internship->hours_saturday ?? 0) }}h"
                                            readonly>
                                        <label for="total_weekly_hours">Total Semanal (máx. 30h)</label>
                                    </div>
                                </div>
                            </div>

                            {{-- Remuneração --}}
                            <h6 class="mb-3 mt-4 border-bottom pb-2">Remuneração</h6>
                            <div class="row m-0 m-0">
                                <div class="col-md-4 mb-3">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="is_remunerated" value="0">
                                        <input class="form-check-input" type="checkbox" id="is_remunerated"
                                            name="is_remunerated" value="1"
                                            {{ old('is_remunerated', $internship->is_remunerated) == '1' || old('is_remunerated', $internship->is_remunerated) === true ? 'checked' : '' }}
                                            onchange="toggleRemunerationFields()">
                                        <label class="form-check-label" for="is_remunerated">
                                            <strong>Estágio Remunerado</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row m-0" id="remuneration-fields" class="d-flex">
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input type="number"
                                            class="form-control @error('grant_value') is-invalid @enderror"
                                            id="grant_value" name="grant_value"
                                            value="{{ old('grant_value', $internship->grant_value ?? '') }}"
                                            placeholder="0,00" min="0" step="0.01"
                                            {{ !(old('is_remunerated', $internship->is_remunerated) == '1' || old('is_remunerated', $internship->is_remunerated) === true) ? 'readonly' : '' }}>
                                        <label for="grant_value">Valor da Bolsa Auxílio (R$)</label>
                                        @error('grant_value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input type="number"
                                            class="form-control @error('transportation_allowance') is-invalid @enderror"
                                            id="transportation_allowance" name="transportation_allowance"
                                            value="{{ old('transportation_allowance', $internship->transportation_allowance ?? '') }}"
                                            placeholder="0,00" min="0" step="0.01"
                                            {{ !(old('is_remunerated', $internship->is_remunerated) == '1' || old('is_remunerated', $internship->is_remunerated) === true) ? 'readonly' : '' }}>
                                        <label for="transportation_allowance">Auxílio Transporte (R$)</label>
                                        @error('transportation_allowance')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row m-0 m-0">
                                <div class="col-12 mb-3">
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
                </div>

                {{-- Avaliação do Supervisor --}}
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingEvaluation">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseEvaluation" aria-expanded="false"
                            aria-controls="collapseEvaluation">
                            <i class="bi bi-clipboard-check me-2"></i>Avaliação do Supervisor
                        </button>
                    </h2>
                    <div id="collapseEvaluation" class="accordion-collapse collapse" aria-labelledby="headingEvaluation">
                        <div class="accordion-body">

                            {{-- Informações Gerais da Avaliação --}}
                            <h6 class="mb-3 mt-4 border-bottom pb-2">Informações Gerais</h6>
                            <div class="row m-0 m-0">
                                <div class="col-md-4 mb-3">
                                    <div class="form-floating">
                                        <select class="form-select" id="evaluation_has_academic_background"
                                            name="evaluation_has_academic_background">
                                            <option value="">Selecione</option>
                                            <option value="Sim"
                                                {{ old('evaluation_has_academic_background', $internship->evaluation_has_academic_background) == 'Sim' ? 'selected' : '' }}>
                                                Sim</option>
                                            <option value="Não"
                                                {{ old('evaluation_has_academic_background', $internship->evaluation_has_academic_background) == 'Não' ? 'selected' : '' }}>
                                                Não</option>
                                        </select>
                                        <label for="evaluation_has_academic_background">Formação Acadêmica</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <div class="form-check form-switch d-flex align-items-center m-0">
                                        <input class="form-check-input form-switch-lg" type="checkbox" role="switch"
                                            id="evaluation_completed_workload_switch"
                                            {{ old('evaluation_completed_workload', $internship->evaluation_completed_workload) == 'Sim' ? 'checked' : '' }}>
                                        <input type="hidden" name="evaluation_completed_workload"
                                            id="evaluation_completed_workload"
                                            value="{{ old('evaluation_completed_workload', $internship->evaluation_completed_workload) }}">
                                        <label class="form-check-label mb-0 ms-2"
                                            for="evaluation_completed_workload_switch">
                                            <strong>Carga Horária Cumprida</strong>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="evaluation_training_course"
                                            name="evaluation_training_course"
                                            value="{{ old('evaluation_training_course', $internship->evaluation_training_course) }}"
                                            placeholder="Curso de formação">
                                        <label for="evaluation_training_course">Curso de Formação</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row m-0 m-0">
                                <div class="col-md-4 mb-3">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="evaluation_education_level"
                                            name="evaluation_education_level"
                                            value="{{ old('evaluation_education_level', $internship->evaluation_education_level) }}"
                                            placeholder="Nível de escolaridade">
                                        <label for="evaluation_education_level">Nível de Escolaridade</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="evaluation_job_role"
                                            name="evaluation_job_role"
                                            value="{{ old('evaluation_job_role', $internship->evaluation_job_role) }}"
                                            placeholder="Função exercida">
                                        <label for="evaluation_job_role">Função Exercida</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="evaluation_experience_time"
                                            name="evaluation_experience_time"
                                            value="{{ old('evaluation_experience_time', $internship->evaluation_experience_time) }}"
                                            placeholder="Tempo de experiência">
                                        <label for="evaluation_experience_time">Tempo de Experiência</label>
                                    </div>
                                </div>
                            </div>

                            {{-- Critérios de Avaliação (1-10) --}}
                            <h6 class="mb-3 mt-4 border-bottom pb-2">Critérios de Avaliação</h6>
                            @php
                                $criteria = [
                                    'evaluation_performance' => '1. Desempenho',
                                    'evaluation_comprehension' => '2. Compreensão',
                                    'evaluation_technical_knowledge' => '3. Conhecimento Técnico',
                                    'evaluation_organization' => '4. Organização',
                                    'evaluation_initiative' => '5. Iniciativa',
                                    'evaluation_attendance' => '6. Assiduidade',
                                    'evaluation_discipline' => '7. Disciplina',
                                    'evaluation_sociability' => '8. Sociabilidade',
                                    'evaluation_cooperation' => '9. Cooperação',
                                    'evaluation_responsibility' => '10. Responsabilidade',
                                ];
                                $options = ['Ótimo', 'Muito Bom', 'Bom', 'Satisfatório', 'Insatisfatório'];
                                $chunks = array_chunk($criteria, 5, true);
                            @endphp

                            <div class="row m-0 m-0">
                                @foreach ($chunks as $chunk)
                                    <div class="col-md-6">
                                        <div class="row m-0 m-0">
                                            @foreach ($chunk as $field => $label)
                                                <div class="col-md-12 mb-3">
                                                    <div class="form-floating">
                                                        <select class="form-select @error($field) is-invalid @enderror"
                                                            id="{{ $field }}" name="{{ $field }}">
                                                            <option value="">Selecione</option>
                                                            @foreach ($options as $option)
                                                                <option value="{{ $option }}"
                                                                    {{ old($field, $internship->{$field}) == $option ? 'selected' : '' }}>
                                                                    {{ $option }}</option>
                                                            @endforeach
                                                        </select>
                                                        <label for="{{ $field }}">{{ $label }}</label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Nota Final --}}
                            <div class="row m-0 m-0">
                                <div class="col-md-3 mb-3">
                                    <div class="form-floating">
                                        <input type="number" class="form-control" id="evaluation_grade"
                                            name="evaluation_grade" step="0.01" min="0"
                                            value="{{ number_format($internship->evaluation_grade, 2) }}"
                                            placeholder="Nota final" readonly>
                                        <label for="evaluation_grade">Nota Final</label>
                                    </div>
                                </div>
                            </div>

                            {{-- Campos de Texto --}}
                            <h6 class="mb-3 mt-4 border-bottom pb-2">Observações</h6>
                            <div class="row m-0 m-0">
                                <div class="col-md-12 mb-3">
                                    <div class="form-floating">
                                        <textarea class="form-control" id="evaluation_considerations" name="evaluation_considerations"
                                            style="height: 100px" placeholder="Considerações">{{ old('evaluation_considerations', $internship->evaluation_considerations) }}</textarea>
                                        <label for="evaluation_considerations">Considerações</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row m-0 m-0">
                                <div class="col-md-12 mb-3">
                                    <div class="form-floating">
                                        <textarea class="form-control" id="evaluation_suggestions_to_institution"
                                            name="evaluation_suggestions_to_institution" style="height: 100px" placeholder="Sugestões à instituição">{{ old('evaluation_suggestions_to_institution', $internship->evaluation_suggestions_to_institution) }}</textarea>
                                        <label for="evaluation_suggestions_to_institution">Sugestões à Instituição</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row m-0 m-0">
                                <div class="col-md-12 mb-3">
                                    <div class="form-floating">
                                        <textarea class="form-control" id="evaluation_performance_issues" name="evaluation_performance_issues"
                                            style="height: 100px" placeholder="Problemas de desempenho">{{ old('evaluation_performance_issues', $internship->evaluation_performance_issues) }}</textarea>
                                        <label for="evaluation_performance_issues">Problemas de Desempenho</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row m-0 m-0">
                                <div class="col-md-12 mb-3">
                                    <div class="form-floating">
                                        <textarea class="form-control" id="evaluation_other_observations" name="evaluation_other_observations"
                                            style="height: 100px" placeholder="Outras observações">{{ old('evaluation_other_observations', $internship->evaluation_other_observations) }}</textarea>
                                        <label for="evaluation_other_observations">Outras Observações</label>
                                    </div>
                                </div>
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
                    <hr>
                    <div class="d-flex gap-2 mt-3">
                        {{-- Botão Deletar --}}
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                            data-bs-target="#deleteModal">
                            <i class="bi bi-trash me-2"></i>Excluir Estágio
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <script>
            let companiesData = [];

            function buscarDadosConcedente() {
                let identificador = document.getElementById('company_legal_identifier').value;

                if (!identificador) {
                    alert('Por favor, informe o CNPJ/CPF');
                    return;
                }

                fetch('/api/companies?identificador=' + identificador)
                    .then(response => response.json())
                    .then(function(data) {
                        companiesData = data;
                        atualizarSelectEmpresas(data);
                    })
                    .catch(function(error) {
                        alert('Erro ao buscar empresas. Verifique o CNPJ/CPF informado.');
                    });
            }

            function atualizarSelectEmpresas(companies) {
                const selectContainer = document.getElementById('company_select_container');

                if (companies && companies.length > 0) {
                    const selectHTML = `
                        <div class="form-floating">
                            <select class="form-select" id="company_select" name="company_id" onchange="preencherDadosEmpresa()">
                                <option value="">Selecione uma empresa</option>
                                ${companies.map(company =>
                                    `<option value="${company.id}">${company.name}</option>`
                                ).join('')}
                            </select>
                            <label for="company_select">Empresas encontradas (${companies.length})</label>
                        </div>
                    `;
                    selectContainer.innerHTML = selectHTML;
                } else {
                    const noCompanyHTML = `
                        <div class="form-floating">
                            <input type="text" class="form-control" value="Nenhuma empresa encontrada" readonly>
                            <label>Empresas cadastradas</label>
                        </div>
                    `;
                    selectContainer.innerHTML = noCompanyHTML;
                }
            }

            function preencherDadosEmpresa() {
                const selectElement = document.getElementById('company_select');
                const selectedCompanyId = selectElement.value;

                if (!selectedCompanyId) {
                    return;
                }

                const selectedCompany = companiesData.find(company => company.id == selectedCompanyId);

                if (selectedCompany) {
                    document.getElementById('company_name').value = selectedCompany.name || '';
                    document.getElementById('company_phone').value = selectedCompany.phone || '';
                    document.getElementById('company_email').value = selectedCompany.email || '';
                    document.getElementById('company_representative_name').value = selectedCompany.representative_name || '';
                    document.getElementById('company_representative_role').value = selectedCompany.representative_role || '';
                    document.getElementById('field_of_activity').value = selectedCompany.field_of_activity || '';
                    document.getElementById('company_address_street').value = selectedCompany.address_street || '';
                    document.getElementById('company_address_number').value = selectedCompany.address_number || '';
                    document.getElementById('company_address_neighborhood').value = selectedCompany.address_neighborhood || '';
                    document.getElementById('company_address_city').value = selectedCompany.address_city || '';
                    document.getElementById('company_address_state').value = selectedCompany.address_state || '';
                    document.getElementById('company_address_zip').value = selectedCompany.address_zip || '';
                    document.getElementById('professional_council').value = selectedCompany.professional_council || '';
                    document.getElementById('council_registration_number').value = selectedCompany
                        .council_registration_number || '';
                    document.getElementById('process_number').value = selectedCompany.process_number || '';
                }
            }

            function toggleRemunerationFields() {
                const checkbox = document.getElementById('is_remunerated');
                const grant_value_field = document.getElementById('grant_value');
                const transportation_allowance_field = document.getElementById('transportation_allowance');

                if (checkbox.checked) {
                    grant_value_field.readOnly = false;
                    grant_value_field.required = true;
                    transportation_allowance_field.readOnly = false;
                    transportation_allowance_field.required = true;
                } else {
                    grant_value_field.value = '';
                    grant_value_field.readOnly = true;
                    grant_value_field.required = false;
                    transportation_allowance_field.value = '';
                    transportation_allowance_field.readOnly = true;
                    transportation_allowance_field.required = false;
                }
            }

            function toggleLegalGuardianFields() {
                const checkbox = document.getElementById('student_is_adult');
                const legal_guardian_name = document.getElementById('legal_guardian_name');
                const legal_guardian_cpf = document.getElementById('legal_guardian_cpf');
                const legal_guardian_kinship = document.getElementById('legal_guardian_kinship');
                const legal_guardian_kinship_readonly = document.getElementById('legal_guardian_kinship_readonly');
                const legal_guardian_email = document.getElementById('legal_guardian_email');

                if (checkbox.checked) {
                    // Aluno é maior de idade - limpa campos e mostra inputs readonly
                    legal_guardian_name.value = '';
                    legal_guardian_name.readOnly = true;
                    legal_guardian_name.required = false;

                    legal_guardian_cpf.value = '';
                    legal_guardian_cpf.readOnly = true;
                    legal_guardian_cpf.required = false;

                    // Esconde o select e mostra o input readonly
                    legal_guardian_kinship.style.display = 'none';
                    legal_guardian_kinship.value = '';
                    legal_guardian_kinship.required = false;
                    legal_guardian_kinship_readonly.style.display = 'block';

                    legal_guardian_email.value = '';
                    legal_guardian_email.readOnly = true;
                    legal_guardian_email.required = false;
                } else {
                    // Aluno é menor de idade - habilita campos normais
                    legal_guardian_name.readOnly = false;
                    legal_guardian_name.required = true;

                    legal_guardian_cpf.readOnly = false;
                    legal_guardian_cpf.required = true;

                    // Mostra o select e esconde o input readonly
                    legal_guardian_kinship.style.display = 'block';
                    legal_guardian_kinship.required = true;
                    legal_guardian_kinship_readonly.style.display = 'none';

                    legal_guardian_email.readOnly = false;
                    legal_guardian_email.required = false;
                }
            }

            // Controla o switch de carga horária cumprida da avaliação
            function setupEvaluationWorkloadSwitch() {
                const switchElement = document.getElementById('evaluation_completed_workload_switch');
                const hiddenInput = document.getElementById('evaluation_completed_workload');

                if (switchElement && hiddenInput) {
                    // Configura o valor inicial do hidden input baseado no estado do switch
                    hiddenInput.value = switchElement.checked ? 'Sim' : 'Não';

                    // Atualiza quando o switch mudar
                    switchElement.addEventListener('change', function() {
                        hiddenInput.value = this.checked ? 'Sim' : 'Não';
                    });
                }
            }

            // Calcula e atualiza o total de horas semanais
            function calculateTotalWeeklyHours() {
                const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                const maxWeeklyHours = 30;
                let total = 0;

                days.forEach(day => {
                    const input = document.getElementById('hours_' + day);
                    if (input && input.value) {
                        total += parseInt(input.value) || 0;
                    }
                });

                const totalField = document.getElementById('total_weekly_hours');
                totalField.value = total + 'h';

                // Adiciona indicador visual se ultrapassar o limite
                if (total > maxWeeklyHours) {
                    totalField.classList.add('text-danger', 'fw-bold');
                    totalField.classList.remove('bg-light');
                    totalField.classList.add('bg-danger', 'bg-opacity-10');
                } else {
                    totalField.classList.remove('text-danger', 'fw-bold', 'bg-danger', 'bg-opacity-10');
                    totalField.classList.add('bg-light');
                }
            }

            // Adiciona listeners aos campos de horas
            function setupWeeklyHoursListeners() {
                const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

                days.forEach(day => {
                    const input = document.getElementById('hours_' + day);
                    if (input) {
                        input.addEventListener('input', calculateTotalWeeklyHours);
                    }
                });
            }

            // Inicializar os campos quando a página carregar
            document.addEventListener('DOMContentLoaded', function() {
                toggleRemunerationFields();
                toggleLegalGuardianFields();
                setupEvaluationWorkloadSwitch();
                setupWeeklyHoursListeners();
                calculateTotalWeeklyHours();
            });
        </script>

        {{-- Modal Recalcular Data de Término --}}
        <div class="modal fade" id="recalculateEndDateModal" tabindex="-1" aria-labelledby="recalculateEndDateModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('admin.internships.recalculate-end-date', $internship->id) }}" method="POST">
                        @csrf
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="recalculateEndDateModalLabel">
                                <i class="bi bi-calculator me-2"></i>Recalcular Data de Término
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <div class="form-floating">
                                    <input type="date" class="form-control" id="calc_start_date" name="calc_start_date"
                                        value="{{ old('calc_start_date', now()->format('Y-m-d')) }}" required>
                                    <label for="calc_start_date">Data de referência para o cálculo *</label>
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Esta data é usada apenas para o cálculo. A data de início real do estágio não será alterada.
                                </small>
                            </div>
                            <div class="mb-3">
                                <div class="form-floating">
                                    <input type="number" class="form-control" id="remaining_hours" name="remaining_hours"
                                        min="1" max="{{ $internship->required_hours }}" value="{{ old('remaining_hours') }}" placeholder="Horas restantes" required>
                                    <label for="remaining_hours">Horas restantes a cumprir *</label>
                                </div>
                            </div>
                            <div class="alert alert-warning mb-0" role="alert">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                                    <small>
                                        O cálculo utilizará a carga horária semanal atualmente salva. Caso precise
                                        alterá-la, salve o formulário primeiro e então recalcule.
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-calculator me-2"></i>Recalcular
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Excluir --}}
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        Tem certeza que deseja excluir este estágio? Esta ação pode ser desfeita.
                    </div>
                    <div class="modal-footer">
                        <form action="{{ route('admin.internships.destroy', $internship->id) }}" method="POST"
                            class="d-inline">
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
    </div>
@endsection
