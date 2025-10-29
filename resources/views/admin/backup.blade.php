@extends('layouts.auth')

@section('title', 'Backup da Base de Dados')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Backup da Base de Dados</h2>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="bi bi-database-down me-2"></i>Backup do Banco de Dados</h5>
            </div>
            <div class="card-body">
                <p class="card-text">
                    Utilize esta funcionalidade para criar um backup completo do banco de dados da aplicação. O arquivo
                    gerado estará no formato <strong>.zip</strong> e conterá todos os dados necessários para restaurar o
                    sistema. Além disso, o arquivo será salvo nos arquivos do software.
                </p>
                <p><strong>Instruções para Backup:</strong></p>
                <ul>
                    <li>Clique no botão "Fazer Backup Completo" abaixo.</li>
                    <li>Aguarde o download do arquivo <code>NOME-DA-APP-ANO-MES-DIA_HORA-MINUTO-SEGUNDO.zip</code>.</li>
                    <li>Armazene o arquivo em um local seguro.</li>
                </ul>

                <hr>

                <p><strong>Instruções para Restauração:</strong></p>
                <ol>
                    <li><strong>Extraia o arquivo .zip</strong> que você baixou.</li>
                    <li>Dentro da pasta extraída, você encontrará uma subpasta chamada <code>db-dumps</code>. Dentro dela, haverá um arquivo com final <code>.sql</code>. <strong>Este é o seu backup do banco de dados.</strong></li>
                    <li>Em um ambiente novo, execute o comando <code>php artisan migrate</code> para criar a estrutura inicial do banco de dados.</li>
                    <li>Importe o arquivo <code>.sql</code> para o seu banco de dados.</li>
                </ol>

                <form method="POST" action="{{ route('admin.backup.create') }}"
                    class="d-flex justify-content-end no-spinner">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-cloud-download me-2"></i>Fazer Backup
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
