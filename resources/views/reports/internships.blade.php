@extends('layouts.auth')

@section('title', 'Exportar Dados de Estágios')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Exportar Dados de Estágios</h2>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('reports.internships.export') }}" method="POST" class="needs-validation no-spinner" novalidate>
                    @csrf

                    <div class="row m-0 m-0">
                        {{-- Filtro de Cursos para Admin e Coordenador --}}
                        @if (Auth::user()->can('is-admin') || Auth::user()->can('is-coordenador'))
                            <div class="col-md-4 mb-3">
                                <div class="form-floating">
                                    <select class="form-select @error('course_id') is-invalid @enderror" id="course_id"
                                        name="course_id" required>
                                        <option value="all_courses" selected>
                                            @can('is-admin')
                                                Todos os Cursos
                                            @else
                                                Todos os meus Cursos
                                            @endcan
                                        </option>

                                        @can('is-coordenador')
                                            <option value="my_advisees"
                                                {{ old('course_id') == 'my_advisees' ? 'selected' : '' }}>
                                                Apenas Meus Orientandos (todos os cursos)
                                            </option>
                                        @endcan

                                        {{-- Lista de Cursos --}}
                                        @foreach ($courses as $course)
                                            <option value="{{ $course->id }}"
                                                {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                                {{ $course->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="course_id">Filtro Principal *</label>
                                    @error('course_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        {{-- Filtros de Data --}}
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                    id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                                <label for="start_date">Data de Início (a partir de) *</label>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                    id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                                <label for="end_date">Data de Fim (até) *</label>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <x-form-info-alert>
                      <li>
                          <strong>Como visualizar o arquivo CSV:</strong>
                          <ul>
                              <li>
                                  <strong>No Computador:</strong> Na maioria dos casos, basta abrir o arquivo baixado com um programa de planilhas como o Microsoft Excel ou LibreOffice Calc.
                              </li>
                              <li>
                                  <strong>No Google Sheets (Planilhas Google):</strong>
                                  <ol>
                                      <li>Abra o <a href="https://sheets.google.com" target="_blank">Google Sheets</a>.</li>
                                      <li>Clique em "Abrir seletor de arquivos" (ícone de pasta)</li>
                                      <li>Vá para a aba "Upload" e selecione o arquivo CSV que você baixou.</li>
                                  </ol>
                              </li>
                          </ul>
                      </li>
                      <li>
                          <strong>Atenção:</strong> Se os caracteres com acentos (como "ç" ou "ã") não aparecerem corretamente, na tela de importação do seu programa de planilhas, certifique-se de que a codificação de caracteres está definida como <strong>"UTF-8"</strong>.
                      </li>
                  </x-form-info-alert>

                    {{-- Botões --}}
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-download"></i>
                            Exportar CSV
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
