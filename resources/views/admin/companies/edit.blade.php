@extends('layouts.auth')

@section('title', 'Editar Parte Concedente')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Editar Parte Concedente</h2>
            <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left-circle me-2"></i>Voltar
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form class="needs-validation" action="{{ route('admin.companies.update', $company->id) }}" method="POST"
                    novalidate>
                    @csrf
                    @method('PUT')

                    {{-- Seção de Identificação --}}
                    <h5 class="mb-3 border-bottom pb-2">Identificação</h5>
                    <div class="row m-0 m-0">
                        <div class="col-md-8 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', $company->name) }}"
                                    placeholder="Ex: Empresa Exemplo Ltda" required>
                                <label for="name">Nome / Razão Social *</label>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">O campo nome é obrigatório.</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('legal_identifier') is-invalid @enderror"
                                    id="legal_identifier" name="legal_identifier"
                                    value="{{ old('legal_identifier', $company->legal_identifier) }}"
                                    placeholder="CPF ou CNPJ (apenas números)" required>
                                <label for="legal_identifier">CPF / CNPJ *</label>
                                @error('legal_identifier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">O campo CPF/CNPJ é obrigatório.</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Seção de Endereço --}}
                    <h5 class="mb-3 pt-3 border-bottom pb-2">Endereço</h5>
                    <div class="row m-0 m-0">
                        <div class="col-md-8 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('address_street') is-invalid @enderror"
                                    id="address_street" name="address_street"
                                    value="{{ old('address_street', $company->address_street) }}"
                                    placeholder="Ex: Rua Principal" required>
                                <label for="address_street">Rua *</label>
                                @error('address_street')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">O campo rua é obrigatório.</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('address_number') is-invalid @enderror"
                                    id="address_number" name="address_number"
                                    value="{{ old('address_number', $company->address_number) }}" placeholder="Ex: 123"
                                    required>
                                <label for="address_number">Número *</label>
                                @error('address_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">O campo número é obrigatório.</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row m-0 m-0">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('address_neighborhood') is-invalid @enderror"
                                    id="address_neighborhood" name="address_neighborhood"
                                    value="{{ old('address_neighborhood', $company->address_neighborhood) }}"
                                    placeholder="Ex: Centro" required>
                                <label for="address_neighborhood">Bairro *</label>
                                @error('address_neighborhood')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">O campo bairro é obrigatório.</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('address_city') is-invalid @enderror"
                                    id="address_city" name="address_city"
                                    value="{{ old('address_city', $company->address_city) }}"
                                    placeholder="Ex: Santo Augusto" required>
                                <label for="address_city">Cidade *</label>
                                @error('address_city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">O campo cidade é obrigatório.</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row m-0 m-0">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <select class="form-select @error('address_state') is-invalid @enderror" id="address_state"
                                    name="address_state" required>
                                    <option value="" disabled>Selecione o estado</option>
                                    @foreach (['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'] as $state)
                                        <option value="{{ $state }}"
                                            {{ old('address_state', $company->address_state) == $state ? 'selected' : '' }}>
                                            {{ $state }}</option>
                                    @endforeach
                                </select>
                                <label for="address_state">Estado *</label>
                                @error('address_state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">O campo estado é obrigatório.</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('address_zip') is-invalid @enderror"
                                    id="address_zip" name="address_zip"
                                    value="{{ old('address_zip', $company->address_zip) }}" placeholder="Ex: 98590-000"
                                    required>
                                <label for="address_zip">CEP *</label>
                                @error('address_zip')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">O campo CEP é obrigatório.</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Seção de Representante e Contato --}}
                    <h5 class="mb-3 pt-3 border-bottom pb-2">Representante e Contato</h5>
                    <div class="row m-0 m-0">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('representative_name') is-invalid @enderror"
                                    id="representative_name" name="representative_name"
                                    value="{{ old('representative_name', $company->representative_name) }}"
                                    placeholder="Ex: João da Silva" required>
                                <label for="representative_name">Nome do Representante Legal *</label>
                                @error('representative_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">O campo nome do representante é obrigatório.</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('representative_role') is-invalid @enderror"
                                    id="representative_role" name="representative_role"
                                    value="{{ old('representative_role', $company->representative_role) }}"
                                    placeholder="Ex: Sócio-Administrador" required>
                                <label for="representative_role">Cargo do Representante *</label>
                                @error('representative_role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">O campo cargo do representante é obrigatório.</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row m-0 m-0">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                    id="phone" name="phone" value="{{ old('phone', $company->phone) }}"
                                    placeholder="Ex: (55) 99999-9999">
                                <label for="phone">Telefone</label>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email', $company->email) }}"
                                    placeholder="Ex: contato@empresa.com">
                                <label for="email">E-mail</label>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Seção de Informações Adicionais --}}
                    <h5 class="mb-3 pt-3 border-bottom pb-2">Informações Adicionais</h5>
                    <div class="row m-0 m-0">
                        <div class="col-md-12 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('field_of_activity') is-invalid @enderror"
                                    id="field_of_activity" name="field_of_activity"
                                    value="{{ old('field_of_activity', $company->field_of_activity) }}"
                                    placeholder="Ex: Desenvolvimento de Software" required>
                                <label for="field_of_activity">Área de Atuação *</label>
                                @error('field_of_activity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">O campo área de atuação é obrigatório.</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row m-0 m-0">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text"
                                    class="form-control @error('professional_council') is-invalid @enderror"
                                    id="professional_council" name="professional_council"
                                    value="{{ old('professional_council', $company->professional_council) }}"
                                    placeholder="Ex: CREA-RS">
                                <label for="professional_council">Conselho Profissional (Opcional)</label>
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
                                    value="{{ old('council_registration_number', $company->council_registration_number) }}"
                                    placeholder="Ex: 123456">
                                <label for="council_registration_number">Nº de Registro no Conselho (Opcional)</label>
                                @error('council_registration_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row m-0 m-0">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('process_number') is-invalid @enderror"
                                    id="process_number" name="process_number"
                                    value="{{ old('process_number', $company->process_number) }}"
                                    placeholder="Ex: 23451.000123/2024-01">
                                <label for="process_number">Nº do Processo / Credenciamento (Opcional)</label>
                                @error('process_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
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

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja excluir esta parte concedente? Esta ação pode ser desfeita.
                </div>
                <div class="modal-footer">
                    <form action="{{ route('admin.companies.destroy', $company->id) }}" method="POST" class="d-inline">
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
