@extends('layouts.auth')

@section('title', 'Editar Parte Concedente')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Editar Parte Concedente</h2>
            <x-ui.back-button url="{{ route('admin.companies.index') }}" />
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form class="needs-validation" action="{{ route('admin.companies.update', $company->id) }}" method="POST"
                    novalidate>
                    @csrf
                    @method('PUT')

                    {{-- Seção de Identificação --}}
                    <h5 class="mb-3 border-bottom pb-2">Identificação</h5>
                    <div class="row m-0">
                        <div class="col-md-8 mb-3">
                            <x-form.input name="name" value="{{ $company->name }}" label="Nome / Razão Social *" placeholder="Ex: Empresa Exemplo Ltda" required feedback="O campo nome é obrigatório." />
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-form.input name="legal_identifier" value="{{ $company->legal_identifier }}" label="CPF / CNPJ *" placeholder="CPF ou CNPJ (apenas números)" required feedback="O campo CPF/CNPJ é obrigatório." />
                        </div>
                    </div>

                    {{-- Seção de Endereço --}}
                    <h5 class="mb-3 pt-3 border-bottom pb-2">Endereço</h5>
                    <div class="row m-0">
                        <div class="col-md-8 mb-3">
                            <x-form.input name="address_street" value="{{ $company->address_street }}" label="Rua *" placeholder="Ex: Rua Principal" required feedback="O campo rua é obrigatório." />
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-form.input name="address_number" value="{{ $company->address_number }}" label="Número *" placeholder="Ex: 123" required feedback="O campo número é obrigatório." />
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-6 mb-3">
                            <x-form.input name="address_neighborhood" value="{{ $company->address_neighborhood }}" label="Bairro *" placeholder="Ex: Centro" required feedback="O campo bairro é obrigatório." />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-form.input name="address_city" value="{{ $company->address_city }}" label="Cidade *" placeholder="Ex: Santo Augusto" required feedback="O campo cidade é obrigatório." />
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-6 mb-3">
                            <x-form.select name="address_state" label="Estado *" required feedback="O campo estado é obrigatório.">
                                <option value="" disabled>Selecione o estado</option>
                                @foreach (['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'] as $state)
                                    <option value="{{ $state }}"
                                        {{ old('address_state', $company->address_state) == $state ? 'selected' : '' }}>
                                        {{ $state }}</option>
                                @endforeach
                            </x-form.select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-form.input name="address_zip" value="{{ $company->address_zip }}" label="CEP *" placeholder="Ex: 98590-000" required feedback="O campo CEP é obrigatório." />
                        </div>
                    </div>

                    {{-- Seção de Representante e Contato --}}
                    <h5 class="mb-3 pt-3 border-bottom pb-2">Representante e Contato</h5>
                    <div class="row m-0">
                        <div class="col-md-6 mb-3">
                            <x-form.input name="representative_name" value="{{ $company->representative_name }}" label="Nome do Representante Legal *" placeholder="Ex: João da Silva" required feedback="O campo nome do representante é obrigatório." />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-form.input name="representative_role" value="{{ $company->representative_role }}" label="Cargo do Representante *" placeholder="Ex: Sócio-Administrador" required feedback="O campo cargo do representante é obrigatório." />
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-6 mb-3">
                            <x-form.input name="phone" value="{{ $company->phone }}" label="Telefone" placeholder="Ex: (55) 99999-9999" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-form.input type="email" name="email" value="{{ $company->email }}" label="E-mail" placeholder="Ex: contato@empresa.com" />
                        </div>
                    </div>

                    {{-- Seção de Informações Adicionais --}}
                    <h5 class="mb-3 pt-3 border-bottom pb-2">Informações Adicionais</h5>
                    <div class="row m-0">
                        <div class="col-md-12 mb-3">
                            <x-form.input name="field_of_activity" value="{{ $company->field_of_activity }}" label="Área de Atuação *" placeholder="Ex: Desenvolvimento de Software" required feedback="O campo área de atuação é obrigatório." />
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-6 mb-3">
                            <x-form.input name="professional_council" value="{{ $company->professional_council }}" label="Conselho Profissional (Opcional)" placeholder="Ex: CREA-RS" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-form.input name="council_registration_number" value="{{ $company->council_registration_number }}" label="Nº de Registro no Conselho (Opcional)" placeholder="Ex: 123456" />
                        </div>
                    </div>
                    <div class="row m-0">
                        <div class="col-md-6 mb-3">
                            <x-form.input name="process_number" value="{{ $company->process_number }}" label="Nº do Processo / Credenciamento (Opcional)" placeholder="Ex: 23451.000123/2024-01" />
                        </div>
                    </div>

                    <x-form-info-alert>
                        <li>Os campos marcados com * são obrigatórios.</li>
                        <li>O CPF/CNPJ deve ser inserido sem pontos, traços ou barras.</li>
                        <li>Para casos como escolas estaduais, cadastre cada escola individualmente, mesmo que o CNPJ seja o
                            mesmo da Secretaria de Educação.</li>
                        <li>O campo "Nº do Processo / Credenciamento" é utilizado para formalizar estágios em propriedades
                            rurais e outros casos específicos.</li>
                    </x-form-info-alert>

                    {{-- Botões --}}
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </a>
                            {{-- Botão para acionar o modal de exclusão --}}
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                data-bs-target="#deleteModal">
                                <i class="bi bi-trash me-2"></i>Excluir
                            </button>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <x-modal.delete action="{{ route('admin.companies.destroy', $company->id) }}" />
@endsection
