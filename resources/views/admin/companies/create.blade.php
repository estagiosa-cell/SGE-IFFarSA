@extends('layouts.auth')

@section('title', 'Cadastrar Parte Concedente')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Cadastrar Parte Concedente</h2>
            <x-ui.back-button url="{{ route('admin.companies.index') }}" />
        </div>

        {{-- Exibir erros de importação se existirem --}}
        @if (session('import_errors'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h6><i class="bi bi-exclamation-triangle me-2"></i>Erros encontrados durante a importação:</h6>
                <ul class="mb-0 mt-2">
                    @foreach (session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                {{-- Tabs de navegação --}}
                <ul class="nav nav-tabs mb-4" id="companyTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual"
                            type="button" role="tab">
                            <i class="bi bi-person-plus me-2"></i>Cadastro Manual
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="import-tab" data-bs-toggle="tab" data-bs-target="#import"
                            type="button" role="tab">
                            <i class="bi bi-file-earmark-arrow-up me-2"></i>Importar CSV
                        </button>
                    </li>
                </ul>

                {{-- Conteúdo das tabs --}}
                <div class="tab-content" id="companyTabsContent">
                    {{-- Tab de Cadastro Manual --}}
                    <div class="tab-pane fade show active" id="manual" role="tabpanel">
                        <form class="needs-validation" action="{{ route('admin.companies.store') }}" method="POST"
                            novalidate>
                            @csrf

                            {{-- Seção de Identificação --}}
                            <h5 class="mb-3 border-bottom pb-2">Identificação</h5>
                            <div class="row m-0">
                                <div class="col-md-8 mb-3">
                                    <x-form.input name="name" label="Nome / Razão Social *" placeholder="Ex: Empresa Exemplo Ltda" required feedback="O campo nome é obrigatório." />
                                </div>
                                <div class="col-md-4 mb-3">
                                    <x-form.input name="legal_identifier" label="CPF / CNPJ *" placeholder="CPF ou CNPJ (apenas números)" required feedback="O campo CPF/CNPJ é obrigatório." />
                                </div>
                            </div>

                            {{-- Seção de Endereço --}}
                            <h5 class="mb-3 pt-3 border-bottom pb-2">Endereço</h5>
                            <div class="row m-0">
                                <div class="col-md-8 mb-3">
                                    <x-form.input name="address_street" label="Rua *" placeholder="Ex: Rua Principal" required feedback="O campo rua é obrigatório." />
                                </div>
                                <div class="col-md-4 mb-3">
                                    <x-form.input name="address_number" label="Número *" placeholder="Ex: 123" required feedback="O campo número é obrigatório." />
                                </div>
                            </div>
                            <div class="row m-0">
                                <div class="col-md-6 mb-3">
                                    <x-form.input name="address_neighborhood" label="Bairro *" placeholder="Ex: Centro" required feedback="O campo bairro é obrigatório." />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <x-form.input name="address_city" label="Cidade *" placeholder="Ex: Santo Augusto" required feedback="O campo cidade é obrigatório." />
                                </div>
                            </div>
                            <div class="row m-0">
                                <div class="col-md-6 mb-3">
                                    <x-form.select name="address_state" label="Estado *" required feedback="O campo estado é obrigatório.">
                                        <option value="" disabled selected>Selecione o estado</option>
                                        @foreach (['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'] as $state)
                                            <option value="{{ $state }}"
                                                {{ old('address_state') == $state ? 'selected' : '' }}>
                                                {{ $state }}</option>
                                        @endforeach
                                    </x-form.select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <x-form.input name="address_zip" label="CEP *" placeholder="Ex: 98590-000" required feedback="O campo CEP é obrigatório." />
                                </div>
                            </div>

                            {{-- Seção de Representante e Contato --}}
                            <h5 class="mb-3 pt-3 border-bottom pb-2">Representante e Contato</h5>
                            <div class="row m-0">
                                <div class="col-md-6 mb-3">
                                    <x-form.input name="representative_name" label="Nome do Representante Legal *" placeholder="Ex: João da Silva" required feedback="O campo nome do representante é obrigatório." />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <x-form.input name="representative_role" label="Cargo do Representante *" placeholder="Ex: Sócio-Administrador" required feedback="O campo cargo do representante é obrigatório." />
                                </div>
                            </div>
                            <div class="row m-0">
                                <div class="col-md-6 mb-3">
                                    <x-form.input name="phone" label="Telefone" placeholder="Ex: (55) 99999-9999" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <x-form.input type="email" name="email" label="E-mail" placeholder="Ex: contato@empresa.com" />
                                </div>
                            </div>

                            {{-- Seção de Informações Adicionais --}}
                            <h5 class="mb-3 pt-3 border-bottom pb-2">Informações Adicionais</h5>
                            <div class="row m-0">
                                <div class="col-md-12 mb-3">
                                    <x-form.input name="field_of_activity" label="Área de Atuação *" placeholder="Ex: Desenvolvimento de Software" required feedback="O campo área de atuação é obrigatório." />
                                </div>
                            </div>
                            <div class="row m-0">
                                <div class="col-md-6 mb-3">
                                    <x-form.input name="professional_council" label="Conselho Profissional (Opcional)" placeholder="Ex: CREA-RS" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <x-form.input name="council_registration_number" label="Nº de Registro no Conselho (Opcional)" placeholder="Ex: 123456" />
                                </div>
                            </div>
                            <div class="row m-0">
                                <div class="col-md-6 mb-3">
                                    <x-form.input name="process_number" label="Nº do Processo / Credenciamento (Opcional)" placeholder="Ex: 23451.000123/2024-01" />
                                </div>
                            </div>

                            <x-form-info-alert>
                                <li>Os campos marcados com * são obrigatórios.</li>
                                <li>O CPF/CNPJ deve ser inserido sem pontos, traços ou barras.</li>
                                <li>Para casos como escolas estaduais, cadastre cada escola individualmente, mesmo que o
                                    CNPJ seja o mesmo da Secretaria de Educação.</li>
                                <li>O campo "Nº do Processo / Credenciamento" é utilizado para formalizar estágios em
                                    propriedades rurais e outros casos específicos.</li>
                            </x-form-info-alert>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-2"></i>Cadastrar
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Tab de Importação CSV --}}
                    <div class="tab-pane fade" id="import" role="tabpanel">
                        <form action="{{ route('admin.companies.import') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="row m-0">
                                <div class="col-12">
                                    <h5 class="mb-3">Importação em Massa via CSV</h5>

                                    <x-form-info-alert>
                                        <li>O arquivo CSV deve seguir o formato correto para garantir a importação
                                            bem-sucedida.
                                            <a href="{{ asset('templates/companies_import_template.csv') }}"
                                                download>Baixe o
                                                modelo aqui</a>.
                                        </li>
                                        <li>Certifique-se de que o arquivo não exceda o tamanho máximo de 20MB.</li>
                                        <li>Revise os dados no arquivo CSV antes de importar para evitar erros.</li>
                                        <li>É permitido importar múltiplas empresas com o mesmo CPF/CNPJ (útil para escolas
                                            estaduais com mesmo CNPJ da Secretaria de Educação).</li>
                                    </x-form-info-alert>

                                    <div class="mb-3">
                                        <label for="csv_file" class="form-label">Selecionar arquivo CSV *</label>
                                        <input type="file"
                                            class="form-control @error('csv_file') is-invalid @enderror" id="csv_file"
                                            name="csv_file" accept=".csv,.txt" required>
                                        @error('csv_file')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Arquivo deve ter no máximo 20MB</div>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary">
                                            <i class="bi bi-x-circle me-2"></i>Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-upload me-2"></i>Importar CSV
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
