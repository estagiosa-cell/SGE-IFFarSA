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
                            <x-form.select id="document_type" name="document_type" label="Tipo de Documento" icon="bi-file-earmark-text" feedback="Por favor, selecione o tipo de documento" required>
                                <option value="" disabled selected>Selecione o tipo de documento</option>
                                <option value="termo-compromisso">Termo de Compromisso Padrão</option>
                                <option value="termo-emater-rs">Termo de Compromisso EMATER/RS</option>
                                <option value="termo-seduc">Termo de Compromisso SEDUC</option>
                                <option value="rescisao">Termo de Rescisão de Estágio</option>
                                <option value="credenciamento">Termo de Credenciamento</option>
                                <option value="termo-aditivo-terceira-clausula">Termo Aditivo - Cláusula Terceira</option>
                            </x-form.select>
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
                <x-ui.accordion-item id="collapseSystem" title="Informações do Sistema" icon="bi-info-circle" show="true">
                    <div class="row m-0">
                        <div class="col-md-6 mb-3">
                            <x-form.select name="advisor_id" label="Orientador *" required>
                                <option value="" disabled>Selecione o orientador</option>
                                @foreach ($advisors as $advisor)
                                    <option value="{{ $advisor->id }}"
                                        {{ old('advisor_id', $internship->advisor_id) == $advisor->id ? 'selected' : '' }}>
                                        {{ $advisor->name }}
                                    </option>
                                @endforeach
                            </x-form.select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-form.select name="status" label="Status do Estágio *" required>
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ old('status', $internship->status?->value) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </x-form.select>
                        </div>
                    </div>
                    <div class="row m-0">
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
                    <div class="row m-0">
                        <div class="col-md-12 mb-3">
                            <x-form.textarea name="notes" label="Observações" placeholder="Observações" style="height: 200px">{{ old('notes', $internship->notes) }}</x-form.textarea>
                        </div>
                    </div>
                </x-ui.accordion-item>

                {{-- Dados do Aluno --}}
                <x-ui.accordion-item id="collapseStudent" title="Dados do Aluno" icon="bi-person">
                    <div class="row m-0">
                        <div class="col-md-8 mb-3">
                            <x-form.input name="student_name" value="{{ $internship->student_name }}" label="Nome Completo *" placeholder="Nome completo" required />
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-form.input type="email" name="student_email" value="{{ $internship->student_email }}" label="E-mail *" placeholder="email@exemplo.com" required />
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-4 mb-3">
                            <x-form.input name="student_registration_number" value="{{ $internship->student_registration_number }}" label="Matrícula *" placeholder="Matrícula" required />
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-form.input name="student_year_semester" value="{{ $internship->student_year_semester }}" label="Ano/Semestre *" placeholder="Ex: 2024/1" required />
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-form.input type="date" name="student_birth_date" value="{{ $internship->student_birth_date?->format('Y-m-d') }}" label="Data de Nascimento *" required />
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-3 mb-3">
                            <x-form.input name="student_rg" value="{{ $internship->student_rg }}" label="RG *" placeholder="RG" required />
                        </div>
                        <div class="col-md-3 mb-3">
                            <x-form.input name="student_rg_issuer" value="{{ $internship->student_rg_issuer }}" label="Órgão Emissor *" placeholder="Órgão emissor" required />
                        </div>
                        <div class="col-md-3 mb-3">
                            <x-form.input type="date" name="student_rg_issue_date" value="{{ $internship->student_rg_issue_date?->format('Y-m-d') }}" label="Data de Emissão *" required />
                        </div>
                        <div class="col-md-3 mb-3">
                            <x-form.input name="student_cpf" value="{{ $internship->student_cpf }}" label="CPF *" placeholder="CPF" required />
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-4 mb-3">
                            <x-form.input name="student_phone" value="{{ $internship->student_phone }}" label="Telefone *" placeholder="Telefone" required />
                        </div>
                    </div>

                    {{-- Endereço do Aluno --}}
                    <h6 class="mb-3 mt-4 border-bottom pb-2">Endereço</h6>
                    <div class="row m-0">
                        <div class="col-md-8 mb-3">
                            <x-form.input name="student_address_street" value="{{ $internship->student_address_street }}" label="Rua *" placeholder="Rua" required />
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-form.input name="student_address_number" value="{{ $internship->student_address_number }}" label="Número *" placeholder="Número" required />
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-6 mb-3">
                            <x-form.input name="student_address_neighborhood" value="{{ $internship->student_address_neighborhood }}" label="Bairro *" placeholder="Bairro" required />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-form.input name="student_address_city" value="{{ $internship->student_address_city }}" label="Cidade *" placeholder="Cidade" required />
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-6 mb-3">
                            <x-form.select name="student_address_state" label="Estado *" required>
                                <option value="" disabled>Selecione o estado</option>
                                @foreach (['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'] as $state)
                                    <option value="{{ $state }}"
                                        {{ old('student_address_state', $internship->student_address_state) == $state ? 'selected' : '' }}>
                                        {{ $state }}
                                    </option>
                                @endforeach
                            </x-form.select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-form.input name="student_address_zip" value="{{ $internship->student_address_zip }}" label="CEP *" placeholder="CEP" required />
                        </div>
                    </div>
                </x-ui.accordion-item>

                {{-- Dados do Responsável Legal --}}
                <x-ui.accordion-item id="collapseLegalGuardian" title="Dados do Responsável Legal" icon="bi-person-check">
                    <div class="row m-0">
                        <div class="col-md-4 mb-3">
                            <x-form.switch name="student_is_adult" label="Aluno Maior de Idade" id="student_is_adult" onchange="toggleLegalGuardianFields()" value="1" :checked="old('student_is_adult', $internship->student_is_adult) == '1' || old('student_is_adult', $internship->student_is_adult) === true" />
                        </div>
                    </div>
                    @php
                        $isAdult = old('student_is_adult', $internship->student_is_adult) == '1' || old('student_is_adult', $internship->student_is_adult) === true;
                    @endphp
                    <div class="row m-0" id="legal-guardian-fields">
                        <div class="col-md-8 mb-3">
                            <x-form.input name="legal_guardian_name" value="{{ $internship->legal_guardian_name }}" label="Nome do Responsável Legal" placeholder="Nome do responsável" :readonly="$isAdult" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-form.input name="legal_guardian_cpf" value="{{ $internship->legal_guardian_cpf }}" label="CPF" placeholder="CPF" :readonly="$isAdult" />
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-6 mb-3">
                            <div id="kinship_select_wrapper" style="{{ $isAdult ? 'display: none;' : '' }}">
                                <x-form.select name="legal_guardian_kinship" label="Parentesco" id="legal_guardian_kinship">
                                    <option value="">Selecione o parentesco</option>
                                    <option value="Pai" {{ old('legal_guardian_kinship', $internship->legal_guardian_kinship) == 'Pai' ? 'selected' : '' }}>Pai</option>
                                    <option value="Mãe" {{ old('legal_guardian_kinship', $internship->legal_guardian_kinship) == 'Mãe' ? 'selected' : '' }}>Mãe</option>
                                    <option value="Outro" {{ old('legal_guardian_kinship', $internship->legal_guardian_kinship) == 'Outro' ? 'selected' : '' }}>Outro</option>
                                </x-form.select>
                            </div>
                            <div id="kinship_input_wrapper" style="{{ $isAdult ? '' : 'display: none;' }}">
                                <x-form.input name="legal_guardian_kinship_readonly" label="Parentesco" id="legal_guardian_kinship_readonly" placeholder="Não aplicável (maior de idade)" readonly />
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-form.input type="email" name="legal_guardian_email" value="{{ $internship->legal_guardian_email }}" label="E-mail" placeholder="E-mail" :readonly="$isAdult" />
                        </div>
                    </div>
                </x-ui.accordion-item>

                {{-- Dados da Empresa/Parte Concedente --}}
                <x-ui.accordion-item id="collapseCompany" title="Dados da Parte Concedente" icon="bi-building">
                    <div class="row m-0">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex gap-2 align-items-start">
                                <div class="flex-grow-1">
                                    <x-form.input name="company_legal_identifier" value="{{ $internship->company_legal_identifier }}" label="CNPJ/CPF *" placeholder="CPF (11 dígitos) ou CNPJ (14 caracteres)" required />
                                </div>
                                <button type="button" class="btn btn-outline-primary" style="height: 58px;" onclick="buscarDadosConcedente()" title="Buscar Dados">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div id="company_select_container">
                                <div class="form-floating">
                                    <input type="text" class="form-control" value="Clique no botão ao lado para buscar" readonly>
                                    <label>Empresas cadastradas</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-12 mb-3">
                            <x-form.input name="company_name" value="{{ $internship->company_name }}" label="Nome da Empresa/Instituição *" placeholder="Razão social" required />
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-6 mb-3">
                            <x-form.input name="company_representative_name" value="{{ $internship->company_representative_name }}" label="Representante Legal *" placeholder="Nome do representante" required />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-form.input name="company_representative_role" value="{{ $internship->company_representative_role }}" label="Cargo do Representante *" placeholder="Cargo do representante" required />
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-6 mb-3">
                            <x-form.input name="company_phone" value="{{ $internship->company_phone }}" label="Telefone" placeholder="Telefone" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-form.input type="email" name="company_email" value="{{ $internship->company_email }}" label="E-mail" placeholder="E-mail" />
                        </div>
                    </div>

                    {{-- Endereço da Empresa --}}
                    <h6 class="mb-3 mt-4 border-bottom pb-2">Endereço</h6>
                    <div class="row m-0">
                        <div class="col-md-8 mb-3">
                            <x-form.input name="company_address_street" value="{{ $internship->company_address_street }}" label="Rua" placeholder="Rua" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-form.input name="company_address_number" value="{{ $internship->company_address_number }}" label="Número" placeholder="Número" />
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-6 mb-3">
                            <x-form.input name="company_address_neighborhood" value="{{ $internship->company_address_neighborhood }}" label="Bairro" placeholder="Bairro" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-form.input name="company_address_city" value="{{ $internship->company_address_city }}" label="Cidade" placeholder="Cidade" />
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-6 mb-3">
                            <x-form.select name="company_address_state" label="Estado">
                                <option value="" disabled selected>Selecione o estado</option>
                                @foreach (['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'] as $state)
                                    <option value="{{ $state }}"
                                        {{ old('company_address_state', $internship->company_address_state) == $state ? 'selected' : '' }}>
                                        {{ $state }}
                                    </option>
                                @endforeach
                            </x-form.select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-form.input name="company_address_zip" value="{{ $internship->company_address_zip }}" label="CEP" placeholder="CEP" />
                        </div>
                    </div>

                    {{-- Informações Adicionais da Empresa --}}
                    <h6 class="mb-3 mt-4 border-bottom pb-2">Informações Adicionais</h6>
                    <div class="row m-0">
                        <div class="col-md-6 mb-3">
                            <x-form.input name="professional_council" value="{{ $internship->professional_council }}" label="Conselho Profissional" placeholder="Ex: CREA-RS" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-form.input name="council_registration_number" value="{{ $internship->council_registration_number }}" label="Nº de Registro no Conselho" placeholder="Ex: 123456" />
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-6 mb-3">
                            <x-form.input name="process_number" value="{{ $internship->process_number }}" label="Nº do Processo / Credenciamento" placeholder="Ex: 23451.000123/2024-01" />
                            <div class="form-text mt-1">
                                <small>Obrigatório para gerar documentos de credenciamento.</small>
                            </div>
                        </div>
                    </div>
                </x-ui.accordion-item>

                {{-- Dados do Supervisor --}}
                <x-ui.accordion-item id="collapseSupervisor" title="Dados do Supervisor" icon="bi-person-badge">
                    <div class="row m-0">
                        <div class="col-md-8 mb-3">
                            <x-form.input name="supervisor_name" value="{{ $internship->supervisor_name }}" label="Nome do Supervisor *" placeholder="Nome do supervisor" required />
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-form.input name="supervisor_phone" value="{{ $internship->supervisor_phone }}" label="Telefone" placeholder="Telefone" />
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-6 mb-3">
                            <x-form.input type="email" name="supervisor_email" value="{{ $internship->supervisor_email }}" label="E-mail" placeholder="E-mail" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-form.input name="supervisor_role" value="{{ $internship->supervisor_role }}" label="Cargo *" placeholder="Cargo" required />
                        </div>
                    </div>
                </x-ui.accordion-item>

                {{-- Dados do Estágio --}}
                <x-ui.accordion-item id="collapseInternship" title="Dados do Estágio" icon="bi-briefcase">
                    <div class="row m-0">
                        <div class="col-md-5 mb-3">
                            <x-form.input name="internship_type_name" value="{{ $internship->internship_type_name }}" label="Tipo de Estágio *" placeholder="Tipo de estágio" required />
                        </div>
                        <div class="col-md-2 mb-3">
                            <x-form.input type="number" name="internship_type_weight" value="{{ $internship->internship_type_weight ?? 1 }}" label="Peso" min="1" max="10" step="1" />
                            <small class="text-muted">Escala: 1-10</small>
                        </div>
                        <div class="col-md-5 mb-3">
                            <x-form.input name="internship_sector" value="{{ $internship->internship_sector }}" label="Setor" placeholder="Setor" />
                        </div>
                    </div>

                    {{-- Valores dos Conceitos --}}
                    <h6 class="mb-3 mt-3">Valores dos Conceitos</h6>
                    <div class="row m-0">
                        <div class="col-md-2 mb-3">
                            <x-form.input type="number" name="great_value" value="{{ $internship->great_value ?? '' }}" label="Ótimo *" step="0.01" min="0" required />
                        </div>
                        <div class="col-md-2 mb-3">
                            <x-form.input type="number" name="very_good_value" value="{{ $internship->very_good_value ?? '' }}" label="Muito Bom *" step="0.01" min="0" required />
                        </div>
                        <div class="col-md-2 mb-3">
                            <x-form.input type="number" name="good_value" value="{{ $internship->good_value ?? '' }}" label="Bom *" step="0.01" min="0" required />
                        </div>
                        <div class="col-md-3 mb-3">
                            <x-form.input type="number" name="satisfactory_value" value="{{ $internship->satisfactory_value ?? '' }}" label="Satisfatório *" step="0.01" min="0" required />
                        </div>
                        <div class="col-md-3 mb-3">
                            <x-form.input type="number" name="unsatisfactory_value" value="{{ $internship->unsatisfactory_value ?? '' }}" label="Insatisfatório *" step="0.01" min="0" required />
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-4 mb-3">
                            <x-form.input type="date" name="start_date" value="{{ $internship->start_date?->format('Y-m-d') }}" label="Data de Início *" required />
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-form.input type="date" name="end_date" value="{{ $internship->end_date?->format('Y-m-d') }}" label="Data de Término *" required />
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-form.input type="number" name="required_hours" value="{{ $internship->required_hours }}" label="Horas Obrigatórias *" placeholder="Horas obrigatórias" required />
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-12 mb-3">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#recalculateEndDateModal">
                                <i class="bi bi-calculator me-2"></i>Recalcular Data de Término
                            </button>
                        </div>
                    </div>

                    {{-- Carga Horária Semanal --}}
                    <h6 class="mb-3 mt-4 border-bottom pb-2">Carga Horária Semanal</h6>
                    
                    <div class="row m-0 mb-3">
                        <div class="col-md-12">
                            <input type="hidden" name="has_workload_exception" value="0">
                            <x-form.switch name="has_workload_exception" label="Liberar exceção de limite máximo de carga horária" id="has_workload_exception" onchange="toggleWorkloadException()" value="1" :checked="old('has_workload_exception', $internship->has_workload_exception) == '1' || old('has_workload_exception', $internship->has_workload_exception) === true" />
                            <small class="text-muted d-block ms-5">Isso desativa as travas de horas diárias (máx. 6h) e semanais (máx. 30h) no sistema.</small>
                        </div>
                    </div>

                    <div class="row m-0">
                        @foreach(['sunday' => 'Domingo', 'monday' => 'Segunda', 'tuesday' => 'Terça', 'wednesday' => 'Quarta'] as $day => $label)
                            <div class="col-md-3 col-6 mb-3">
                                <x-form.input type="number" name="hours_{{ $day }}" value="{{ $internship->{'hours_'.$day} ?? '' }}" label="{{ $label }}" placeholder="0" step="1" min="0" max="6" />
                            </div>
                        @endforeach
                    </div>
                    <div class="row m-0">
                        @foreach(['thursday' => 'Quinta', 'friday' => 'Sexta', 'saturday' => 'Sábado'] as $day => $label)
                            <div class="col-md-3 col-6 mb-3">
                                <x-form.input type="number" name="hours_{{ $day }}" value="{{ $internship->{'hours_'.$day} ?? '' }}" label="{{ $label }}" placeholder="0" step="1" min="0" max="6" />
                            </div>
                        @endforeach
                        <div class="col-md-3 col-6 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control bg-light" id="total_weekly_hours"
                                    value="{{ old('hours_sunday', $internship->hours_sunday ?? 0) + old('hours_monday', $internship->hours_monday ?? 0) + old('hours_tuesday', $internship->hours_tuesday ?? 0) + old('hours_wednesday', $internship->hours_wednesday ?? 0) + old('hours_thursday', $internship->hours_thursday ?? 0) + old('hours_friday', $internship->hours_friday ?? 0) + old('hours_saturday', $internship->hours_saturday ?? 0) }}h"
                                    readonly>
                                <label id="total_weekly_hours_label" for="total_weekly_hours">Total Semanal (máx. 30h)</label>
                            </div>
                        </div>
                    </div>

                    {{-- Remuneração --}}
                    <h6 class="mb-3 mt-4 border-bottom pb-2">Remuneração</h6>
                    <div class="row m-0">
                        <div class="col-md-4 mb-3">
                            <x-form.switch name="is_remunerated" label="Estágio Remunerado" id="is_remunerated" onchange="toggleRemunerationFields()" value="1" :checked="old('is_remunerated', $internship->is_remunerated) == '1' || old('is_remunerated', $internship->is_remunerated) === true" />
                        </div>
                    </div>
                    @php
                        $isRemunerated = old('is_remunerated', $internship->is_remunerated) == '1' || old('is_remunerated', $internship->is_remunerated) === true;
                    @endphp
                    <div class="row m-0" id="remuneration-fields">
                        <div class="col-md-6 mb-3">
                            <x-form.input type="number" name="grant_value" value="{{ $internship->grant_value ?? '' }}" label="Valor da Bolsa Auxílio (R$)" placeholder="0,00" min="0" step="0.01" :readonly="!$isRemunerated" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-form.input type="number" name="transportation_allowance" value="{{ $internship->transportation_allowance ?? '' }}" label="Auxílio Transporte (R$)" placeholder="0,00" min="0" step="0.01" :readonly="!$isRemunerated" />
                        </div>
                    </div>

                    <div class="row m-0">
                        <div class="col-12 mb-3">
                            <x-form.textarea name="activities" label="Atividades *" placeholder="Descrição das atividades" style="height: 120px" required>{{ old('activities', $internship->activities) }}</x-form.textarea>
                        </div>
                    </div>
                </x-ui.accordion-item>

                {{-- Avaliação do Supervisor --}}
                <x-ui.accordion-item id="collapseEvaluation" title="Avaliação do Supervisor" icon="bi-clipboard-check">
                    {{-- Informações Gerais da Avaliação --}}
                    <h6 class="mb-3 mt-4 border-bottom pb-2">Informações Gerais</h6>
                    <div class="row m-0">
                        <div class="col-md-4 mb-3">
                            <x-form.select name="evaluation_has_academic_background" label="Formação Acadêmica">
                                <option value="">Selecione</option>
                                <option value="Sim" {{ old('evaluation_has_academic_background', $internship->evaluation_has_academic_background) == 'Sim' ? 'selected' : '' }}>Sim</option>
                                <option value="Não" {{ old('evaluation_has_academic_background', $internship->evaluation_has_academic_background) == 'Não' ? 'selected' : '' }}>Não</option>
                            </x-form.select>
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
                            <x-form.input name="evaluation_training_course" value="{{ $internship->evaluation_training_course }}" label="Curso de Formação" placeholder="Curso de formação" />
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-4 mb-3">
                            <x-form.input name="evaluation_education_level" value="{{ $internship->evaluation_education_level }}" label="Nível de Escolaridade" placeholder="Nível de escolaridade" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-form.input name="evaluation_job_role" value="{{ $internship->evaluation_job_role }}" label="Função Exercida" placeholder="Função exercida" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-form.input name="evaluation_experience_time" value="{{ $internship->evaluation_experience_time }}" label="Tempo de Experiência" placeholder="Tempo de experiência" />
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

                    <div class="row m-0">
                        @foreach ($chunks as $chunk)
                            <div class="col-md-6">
                                <div class="row m-0">
                                    @foreach ($chunk as $field => $label)
                                        <div class="col-md-12 mb-3">
                                            <x-form.select name="{{ $field }}" label="{{ $label }}">
                                                <option value="">Selecione</option>
                                                @foreach ($options as $option)
                                                    <option value="{{ $option }}" {{ old($field, $internship->{$field}) == $option ? 'selected' : '' }}>
                                                        {{ $option }}
                                                    </option>
                                                @endforeach
                                            </x-form.select>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Nota Final --}}
                    <div class="row m-0">
                        <div class="col-md-3 mb-3">
                            <x-form.input type="number" name="evaluation_grade" value="{{ number_format($internship->evaluation_grade, 2) }}" label="Nota Final" placeholder="Nota final" step="0.01" min="0" readonly />
                        </div>
                    </div>

                    {{-- Campos de Texto --}}
                    <h6 class="mb-3 mt-4 border-bottom pb-2">Observações</h6>
                    <div class="row m-0">
                        <div class="col-md-12 mb-3">
                            <x-form.textarea name="evaluation_considerations" label="Considerações" placeholder="Considerações" style="height: 100px">{{ old('evaluation_considerations', $internship->evaluation_considerations) }}</x-form.textarea>
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-12 mb-3">
                            <x-form.textarea name="evaluation_suggestions_to_institution" label="Sugestões à Instituição" placeholder="Sugestões à instituição" style="height: 100px">{{ old('evaluation_suggestions_to_institution', $internship->evaluation_suggestions_to_institution) }}</x-form.textarea>
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-12 mb-3">
                            <x-form.textarea name="evaluation_performance_issues" label="Problemas de Desempenho" placeholder="Problemas de desempenho" style="height: 100px">{{ old('evaluation_performance_issues', $internship->evaluation_performance_issues) }}</x-form.textarea>
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-12 mb-3">
                            <x-form.textarea name="evaluation_other_observations" label="Outras Observações" placeholder="Outras observações" style="height: 100px">{{ old('evaluation_other_observations', $internship->evaluation_other_observations) }}</x-form.textarea>
                        </div>
                    </div>
                </x-ui.accordion-item>

                {{-- Períodos de Pausa --}}
                <x-ui.accordion-item id="collapsePauses" title="Períodos de Pausa ({{ $internship->pauses->count() }})" icon="bi-pause-circle">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Pausas Cadastradas</h6>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPauseModal">
                            <i class="bi bi-plus-circle me-1"></i> Adicionar Nova Pausa
                        </button>
                    </div>

                    @if ($internship->pauses->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Período</th>
                                        <th>Motivo</th>
                                        <th width="15%" class="text-end">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($internship->pauses->sortBy('start_date') as $pause)
                                        <tr>
                                            <td class="align-middle">
                                                <i class="bi bi-calendar-range me-1 text-muted"></i>
                                                {{ $pause->start_date->format('d/m/Y') }} — {{ $pause->end_date->format('d/m/Y') }}
                                            </td>
                                            <td class="align-middle text-muted">
                                                {{ $pause->reason ?? '—' }}
                                            </td>
                                            <td class="text-end align-middle">
                                                <button type="button" class="btn btn-sm btn-outline-danger py-0" title="Excluir"
                                                    data-bs-toggle="modal" data-bs-target="#deletePauseModal-{{ $pause->id }}">
                                                    <i class="bi bi-trash"></i> Excluir
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-muted text-center py-3">
                            <i class="bi bi-info-circle me-1"></i> Nenhuma pausa cadastrada para este estágio.
                        </div>
                    @endif
                </x-ui.accordion-item>

                @php
                    $amendments = $internship->amendments()->withTrashed()->get();
                @endphp
                @if($amendments->isNotEmpty())
                    <x-ui.accordion-item id="collapseAmendments" title="Histórico de Aditivos ({{ $amendments->count() }})" icon="bi-clock-history">
                        <div class="d-flex justify-content-end mb-2 gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary active" onclick="filterAmendments('all', this)">Todos</button>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="filterAmendments('active', this)">Válidos</button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="filterAmendments('trashed', this)">Excluídos</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="10%">Status</th>
                                        <th>Data</th>
                                        <th width="20%" class="text-end">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($amendments as $amendment)
                                        @php
                                            $statusClass = $amendment->trashed() ? 'amendment-trashed' : 'amendment-active';
                                        @endphp
                                        <tr class="amendment-item {{ $statusClass }}">
                                            <td class="align-middle" width="10%">
                                                @if($amendment->trashed())
                                                    <span class="badge bg-danger">Excluído</span>
                                                @else
                                                    <span class="badge bg-success">Válido</span>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                Aditivo gerado em {{ $amendment->created_at->format('d/m/Y \à\s H:i') }}
                                            </td>
                                            <td class="text-end align-middle" width="20%">
                                                @if ($amendment->trashed())
                                                    <button type="button" class="btn btn-sm btn-outline-success py-0" title="Restaurar" onclick="document.getElementById('form-amendment-restore-{{ $amendment->id }}').submit();">
                                                        <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-outline-danger py-0" title="Excluir" onclick="if(confirm('Tem certeza que deseja excluir este aditivo do histórico?')) document.getElementById('form-amendment-destroy-{{ $amendment->id }}').submit();">
                                                        <i class="bi bi-trash"></i> Excluir
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-ui.accordion-item>
                @endif
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



        @if(isset($amendments) && $amendments->isNotEmpty())
            {{-- Formulários ocultos para as ações dos aditivos --}}
            @foreach($amendments as $amendment)
                @if ($amendment->trashed())
                    <form id="form-amendment-restore-{{ $amendment->id }}" action="{{ route('admin.internship-amendments.restore', $amendment->id) }}" method="POST" class="d-none">
                        @csrf
                        @method('PATCH')
                    </form>
                @else
                    <form id="form-amendment-destroy-{{ $amendment->id }}" action="{{ route('admin.internship-amendments.destroy', $amendment->id) }}" method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                @endif
            @endforeach

            <script>
                function filterAmendments(filter, btn) {
                    const items = document.querySelectorAll('.amendment-item');
                    items.forEach(item => {
                        if (filter === 'all') {
                            item.style.display = '';
                        } else if (filter === 'active') {
                            item.style.display = item.classList.contains('amendment-active') ? '' : 'none';
                        } else if (filter === 'trashed') {
                            item.style.display = item.classList.contains('amendment-trashed') ? '' : 'none';
                        }
                    });

                    // Atualiza botões
                    const buttons = btn.parentElement.querySelectorAll('button');
                    buttons.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                }
            </script>
        @endif

        <script>
            // Validação de datas do modal de pausa
            document.addEventListener('DOMContentLoaded', function() {
                const startDateInput = document.getElementById('modal_pause_start_date');
                const endDateInput = document.getElementById('modal_pause_end_date');

                function validatePauseDates() {
                    if (startDateInput && endDateInput) {
                        const startDate = startDateInput.value;
                        const endDate = endDateInput.value;
                        
                        if (startDate && endDate && endDate < startDate) {
                            endDateInput.setCustomValidity('A data de fim deve ser posterior ou igual à data de início.');
                        } else {
                            endDateInput.setCustomValidity('');
                        }
                    }
                }

                if (startDateInput && endDateInput) {
                    startDateInput.addEventListener('change', validatePauseDates);
                    endDateInput.addEventListener('change', validatePauseDates);
                }
            });

            let companiesData = [];

            function buscarDadosConcedente() {
                // Remove apenas os separadores da máscara (pontos, barras, traços).
                // Preserva letras maiúsculas para suportar o novo CNPJ alfanumérico.
                let inputEl = document.getElementById('company_legal_identifier');
                if (!inputEl) return;

                let identificador = inputEl.value.replace(/[.\-\/\s]/g, '').toUpperCase();

                // Atualiza o valor no input para refletir o valor normalizado.
                inputEl.value = identificador;

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
                if (!selectContainer) return;

                if (companies && companies.length > 0) {
                    const selectHTML = `
                        <x-form.select id="company_select" name="company_id" onchange="preencherDadosEmpresa()" label="Empresas encontradas (${companies.length})">
                            <option value="">Selecione uma empresa</option>
                            ${companies.map(company =>
                                `<option value="${company.id}">${company.name}</option>`
                            ).join('')}
                        </x-form.select>
                    `;
                    selectContainer.innerHTML = selectHTML;
                } else {
                    const noCompanyHTML = `
                        <x-form.input name="no_company" id="no_company" value="Nenhuma empresa encontrada" label="Empresas cadastradas" readonly />
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
                    const setVal = (id, val) => {
                        const el = document.getElementById(id);
                        if (el) el.value = val || '';
                    };

                    setVal('company_name', selectedCompany.name);
                    setVal('company_phone', selectedCompany.phone);
                    setVal('company_email', selectedCompany.email);
                    setVal('company_representative_name', selectedCompany.representative_name);
                    setVal('company_representative_role', selectedCompany.representative_role);
                    setVal('field_of_activity', selectedCompany.field_of_activity);
                    setVal('company_address_street', selectedCompany.address_street);
                    setVal('company_address_number', selectedCompany.address_number);
                                            setVal('company_address_neighborhood', selectedCompany.address_neighborhood);
                    setVal('company_address_city', selectedCompany.address_city);
                    setVal('company_address_state', selectedCompany.address_state);
                    setVal('company_address_zip', selectedCompany.address_zip);
                    setVal('professional_council', selectedCompany.professional_council);
                    setVal('council_registration_number', selectedCompany.council_registration_number);
                    setVal('process_number', selectedCompany.process_number);
                }
            }

            function toggleRemunerationFields() {
                const isRemunerated = document.getElementById('is_remunerated').checked;
                const grantValueInput = document.getElementById('grant_value');
                const transportAllowanceInput = document.getElementById('transportation_allowance');

                if (isRemunerated) {
                    grantValueInput.removeAttribute('readonly');
                    transportAllowanceInput.removeAttribute('readonly');
                } else {
                    grantValueInput.setAttribute('readonly', 'readonly');
                    transportAllowanceInput.setAttribute('readonly', 'readonly');
                    grantValueInput.value = '';
                    transportAllowanceInput.value = '';
                }
            }

            function toggleWorkloadException() {
                const hasException = document.getElementById('has_workload_exception').checked;
                const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                const label = document.getElementById('total_weekly_hours_label');

                days.forEach(day => {
                    const input = document.getElementById('hours_' + day);
                    if (input) {
                        if (hasException) {
                            input.removeAttribute('max');
                        } else {
                            input.setAttribute('max', '6');
                        }
                    }
                });

                if (label) {
                    if (hasException) {
                        label.innerText = 'Total Semanal (Exceção Ativa)';
                    } else {
                        label.innerText = 'Total Semanal (máx. 30h)';
                    }
                }
                
                // Recalculate to remove/add visual alert
                calculateTotalWeeklyHours();
            }

            function toggleLegalGuardianFields() {
                const checkbox = document.getElementById('student_is_adult');
                const legal_guardian_name = document.getElementById('legal_guardian_name');
                const legal_guardian_cpf = document.getElementById('legal_guardian_cpf');
                const legal_guardian_kinship = document.getElementById('legal_guardian_kinship');
                const legal_guardian_kinship_readonly = document.getElementById('legal_guardian_kinship_readonly');
                const legal_guardian_email = document.getElementById('legal_guardian_email');

                if (!checkbox || !legal_guardian_name || !legal_guardian_cpf || !legal_guardian_email) return;

                if (checkbox.checked) {
                    // Aluno é maior de idade - limpa campos e mostra inputs readonly
                    legal_guardian_name.value = '';
                    legal_guardian_name.readOnly = true;
                    legal_guardian_name.required = false;
                    legal_guardian_name.classList.remove('is-invalid');

                    legal_guardian_cpf.value = '';
                    legal_guardian_cpf.readOnly = true;
                    legal_guardian_cpf.required = false;
                    legal_guardian_cpf.classList.remove('is-invalid');

                    // Esconde o select e mostra o input readonly
                    document.getElementById('kinship_select_wrapper').style.display = 'none';
                    legal_guardian_kinship.value = '';
                    legal_guardian_kinship.required = false;
                    legal_guardian_kinship.classList.remove('is-invalid');
                    document.getElementById('kinship_input_wrapper').style.display = 'block';

                    legal_guardian_email.value = '';
                    legal_guardian_email.readOnly = true;
                    legal_guardian_email.required = false;
                    legal_guardian_email.classList.remove('is-invalid');
                } else {
                    // Aluno é menor de idade - habilita campos normais
                    legal_guardian_name.readOnly = false;
                    legal_guardian_name.required = true;

                    legal_guardian_cpf.readOnly = false;
                    legal_guardian_cpf.required = true;

                    // Mostra o select e esconde o input readonly
                    document.getElementById('kinship_select_wrapper').style.display = 'block';
                    legal_guardian_kinship.required = true;
                    document.getElementById('kinship_input_wrapper').style.display = 'none';

                    legal_guardian_email.readOnly = false;
                    legal_guardian_email.required = true;
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
                const hasException = document.getElementById('has_workload_exception')?.checked;
                
                if (!totalField) return;

                totalField.value = total + 'h';

                // Adiciona indicador visual se ultrapassar o limite (e não possuir exceção liberada)
                if (total > maxWeeklyHours && !hasException) {
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
                toggleWorkloadException();
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
                                        value="{{ old('calc_start_date', $internship->start_date && $internship->start_date->isFuture() ? $internship->start_date->format('Y-m-d') : now()->format('Y-m-d')) }}" min="{{ $internship->start_date ? $internship->start_date->format('Y-m-d') : '' }}" required>
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

        {{-- Modal Adicionar Pausa --}}
        <div class="modal fade" id="addPauseModal" tabindex="-1" aria-labelledby="addPauseModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="addPauseForm" action="{{ route('admin.internships.pauses.store', $internship->id) }}" method="POST">
                        @csrf
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="addPauseModalLabel">
                                <i class="bi bi-pause-circle me-2"></i>Adicionar Nova Pausa
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <div class="form-floating">
                                    <input type="date" class="form-control" id="modal_pause_start_date" name="start_date"
                                        value="{{ old('start_date', $internship->start_date && $internship->start_date->isFuture() ? $internship->start_date->format('Y-m-d') : '') }}" min="{{ $internship->start_date ? $internship->start_date->format('Y-m-d') : '' }}" required>
                                    <label for="modal_pause_start_date">Data Início *</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-floating">
                                    <input type="date" class="form-control" id="modal_pause_end_date" name="end_date"
                                        value="{{ old('end_date') }}" required>
                                    <label for="modal_pause_end_date">Data Fim *</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="modal_pause_reason" name="reason"
                                        value="{{ old('reason') }}" placeholder="Ex: Férias, Licença médica" maxlength="255">
                                    <label for="modal_pause_reason">Motivo</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Adicionar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modais de Exclusão de Pausas --}}
        @if ($internship->pauses->isNotEmpty())
            @foreach ($internship->pauses as $pause)
                <x-modal.delete 
                    id="deletePauseModal-{{ $pause->id }}" 
                    action="{{ route('admin.internships.pauses.destroy', [$internship->id, $pause->id]) }}" 
                    message="Tem certeza que deseja excluir a pausa do período {{ $pause->start_date->format('d/m/Y') }} a {{ $pause->end_date->format('d/m/Y') }}? Lembre-se de recalcular a data de término do estágio após a exclusão." 
                />
            @endforeach
        @endif

        {{-- Modal Excluir --}}
        <x-modal.delete 
            id="deleteModal" 
            action="{{ route('admin.internships.destroy', $internship->id) }}" 
            message="Tem certeza que deseja excluir este estágio? Esta ação pode ser desfeita." 
        />
        {{-- Modal de Log de Recálculo --}}
        @if(session('recalculate_log'))
            <div class="modal fade" id="recalculateLogModal" tabindex="-1" aria-labelledby="recalculateLogModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="recalculateLogModalLabel">
                                <i class="bi bi-journal-text me-2"></i>Detalhes do Recálculo
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body p-0">
                            <div class="p-3 bg-light border-bottom">
                                <p class="mb-0 text-muted small">
                                    Abaixo está o registro detalhado dia a dia de como a nova data de término foi calculada, considerando feriados, pausas e a sua carga horária semanal.
                                </p>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th class="ps-3">Data</th>
                                            <th>Status</th>
                                            <th class="text-center">Horas no Dia</th>
                                            <th class="text-center pe-3">Acumulado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(session('recalculate_log') as $log)
                                            @php
                                                $rowClass = '';
                                                $statusText = '';
                                                $icon = '';
                                                
                                                switch($log['type']) {
                                                    case 'work_day':
                                                        $rowClass = 'table-success';
                                                        $statusText = 'Dia Útil (Contabilizado)';
                                                        $icon = 'bi-check-circle-fill text-success';
                                                        break;
                                                    case 'holiday':
                                                        $rowClass = 'table-danger';
                                                        $statusText = 'Feriado (Ignorado)';
                                                        $icon = 'bi-calendar-x text-danger';
                                                        break;
                                                    case 'pause':
                                                        $rowClass = 'table-secondary';
                                                        $statusText = 'Pausa Registrada (Ignorado)';
                                                        $icon = 'bi-pause-circle-fill text-secondary';
                                                        break;
                                                    case 'weekend':
                                                        $rowClass = 'table-light text-muted';
                                                        $statusText = 'Sem carga horária (Ignorado)';
                                                        $icon = 'bi-calendar2-minus text-muted';
                                                        break;
                                                    case 'safety_margin':
                                                        $rowClass = 'table-warning';
                                                        $statusText = 'Margem de Segurança (Adicional)';
                                                        $icon = 'bi-shield-check text-warning';
                                                        break;
                                                }
                                            @endphp
                                            <tr class="{{ $rowClass }}">
                                                <td class="ps-3 align-middle">
                                                    {{ $log['date']->format('d/m/Y') }} 
                                                    <small class="text-muted ms-1">({{ $log['date']->locale('pt_BR')->shortDayName }})</small>
                                                </td>
                                                <td class="align-middle">
                                                    <i class="bi {{ $icon }} me-1"></i> {{ $statusText }}
                                                </td>
                                                <td class="text-center align-middle fw-semibold">
                                                    {{ $log['hours_credited'] > 0 ? '+'.$log['hours_credited'].'h' : '-' }}
                                                </td>
                                                <td class="text-center align-middle pe-3">
                                                    {{ $log['accumulated'] }}h
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var logModal = new bootstrap.Modal(document.getElementById('recalculateLogModal'));
                    logModal.show();
                });
            </script>
        @endif
    </div>
@endsection
