@extends('layouts.auth')

@section('title', 'Criar Usuário')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Cadastrar Usuário</h2>
            <x-ui.back-button url="{{ route('admin.users.index') }}" />
        </div>

        {{-- Cadastro normal --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form class="needs-validation" action="{{ route('admin.users.store') }}" method="POST" novalidate>
                    @csrf
                    {{-- Nome --}}
                    <div class="mb-3">
                        <x-form.input name="name" icon="bi-person" label="Nome *" placeholder="Nome completo" required feedback="O campo nome é obrigatório." />
                    </div>
                    {{-- E-mail --}}
                    <div class="mb-3">
                        <x-form.input type="email" name="email" icon="bi-envelope" label="E-mail *" placeholder="E-mail" required feedback="O campo e-mail é obrigatório." />
                    </div>
                    {{-- Confirmação de E-mail --}}
                    <div class="mb-3">
                        <x-form.input type="email" name="email_confirmation" icon="bi-envelope-check" label="Confirmar E-mail *" placeholder="Confirme o e-mail" required feedback="Confirme o e-mail." />
                    </div>
                    {{-- Papel --}}
                    <div class="mb-3">
                        <x-form.select name="role" icon="bi-person-badge" label="Papel *" required feedback="O campo papel é obrigatório.">
                            <option value="">Selecione o papel</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->value }}" {{ old('role') == $role->value ? 'selected' : '' }}>
                                    {{ $role->label() ?? $role->value }}
                                </option>
                            @endforeach
                        </x-form.select>
                    </div>

                    <x-form-info-alert>
                        <li>O e-mail deve ser válido e único no sistema</li>
                        <li>Os e-mails de confirmação devem ser idênticos</li>
                        <li>Uma senha aleatória será criada</li>
                    </x-form-info-alert>

                    <div class="mt-4 d-flex justify-content-between">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Cadastrar Usuário
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Mensagem de erro na importação --}}
        @if (session('importStatus'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Erro na importação:</strong> {!! nl2br(e(str_replace('<br>', "\n", session('importStatus')))) !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        @endif

        {{-- Cadastro em massa --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body bg-light">
                <div class="alert alert-info mb-3">
                    <strong>Cadastro em massa:</strong> Para cadastrar vários orientadores de uma só vez, faça o
                    upload de um arquivo <code>.csv</code> com as colunas <b>nome</b> e <b>email</b>.<br>
                    Todos os usuários cadastrados por esse método terão o perfil de <b>Orientador</b>.
                    <hr>
                    <b>Instruções para o arquivo CSV:</b>
                    <ul class="mb-1">
                        <li>A primeira linha deve ser o cabeçalho: <code>nome,email</code></li>
                        <li>Cada linha seguinte deve conter o nome completo e o e-mail do usuário.</li>
                        <li>As colunas devem ser separadas por <b>vírgula (,)</b>.</li>
                        <li>Exemplo de conteúdo:
                            <pre class="mb-0">nome,email
Nome,email@exemplo.com
Nome1,email1@exemplo.com
                            </pre>
                        </li>
                    </ul>
                    <span class="text-muted">Apenas arquivos .csv com até 10MB são aceitos.</span>
                </div>
                <form id="csv-upload-form" action="{{ route('admin.users.import') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="input-group">
                        <input type="file" class="form-control" name="file" accept=".csv" required>
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="bi bi-upload me-1"></i>Importar CSV
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
