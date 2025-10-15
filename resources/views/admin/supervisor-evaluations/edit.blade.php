@extends('layouts.auth')

@section('title', 'Gerenciar Avaliação')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Gerenciar Avaliação #{{ $evaluation->id }}</h1>
            <a href="{{ route('admin.supervisor-evaluations.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- Card de Associação ao Estágio --}}
        <div class="card shadow mb-4 border-left-primary">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-link"></i> Associar ao Estágio
                </h6>
            </div>
            <div class="card-body">
                <form id="associateForm" action="{{ route('admin.supervisor-evaluations.associate', $evaluation) }}"
                    method="POST">
                    @csrf
                    <div class="mb-3">
                        <div class="form-floating">
                            <select name="internship_id" id="internship_id" class="form-select" required>
                                <option value="">Selecione um estágio em andamento...</option>
                                @foreach ($internships as $internship)
                                    <option value="{{ $internship->id }}">
                                        {{ $internship->student_name }} -
                                        {{ $internship->course->name ?? 'Sem curso' }} -
                                        {{ $internship->company_name ?? 'Sem empresa' }} -
                                        {{ $internship->supervisor_name ?? 'Sem supervisor' }}
                                    </option>
                                @endforeach
                            </select>
                            <label for="internship_id">Selecione o Estágio <span class="text-danger">*</span></label>
                        </div>
                        <small class="form-text text-muted mt-1">
                            Apenas estágios com status "Em andamento" podem receber avaliações.
                            @if ($evaluation->hasCompletedWorkload())
                                <br><strong class="text-success">Esta avaliação marca a carga horária como cumprida. O
                                    estágio será automaticamente marcado como "Concluído".</strong>
                            @endif
                        </small>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                            data-bs-target="#associateModal">
                            <i class="fas fa-check"></i> Associar e Arquivar Avaliação
                        </button>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                            data-bs-target="#deleteEvaluationModal">
                            <i class="fas fa-trash"></i> Excluir Avaliação
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Card de Edição da Avaliação --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-edit"></i> Editar Dados da Avaliação
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.supervisor-evaluations.update', $evaluation) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Informações Básicas --}}
                    <h5 class="text-primary mb-3">Informações Básicas</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="text" name="student_name" id="student_name" class="form-control"
                                    placeholder="Nome do Estagiário"
                                    value="{{ old('student_name', $evaluation->student_name) }}">
                                <label for="student_name">Nome do Estagiário</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="text" name="supervisor_name" id="supervisor_name" class="form-control"
                                    placeholder="Nome do Supervisor"
                                    value="{{ old('supervisor_name', $evaluation->supervisor_name) }}">
                                <label for="supervisor_name">Nome do Supervisor</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating">
                                <input type="email" name="supervisor_email" id="supervisor_email" class="form-control"
                                    placeholder="E-mail do Supervisor"
                                    value="{{ old('supervisor_email', $evaluation->supervisor_email) }}">
                                <label for="supervisor_email">E-mail do Supervisor</label>
                            </div>
                        </div>
                    </div>

                    {{-- Dados do Supervisor --}}
                    <h5 class="text-primary mb-3 mt-4">Dados do Supervisor</h5>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="form-floating">
                                <select name="has_academic_background" id="has_academic_background" class="form-select">
                                    <option value="">Selecione...</option>
                                    <option value="Sim"
                                        {{ old('has_academic_background', $evaluation->has_academic_background) === 'Sim' ? 'selected' : '' }}>
                                        Sim</option>
                                    <option value="Não"
                                        {{ old('has_academic_background', $evaluation->has_academic_background) === 'Não' ? 'selected' : '' }}>
                                        Não</option>
                                </select>
                                <label for="has_academic_background">Formação Acadêmica na Área?</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="form-floating">
                                <input type="text" name="training_course" id="training_course" class="form-control"
                                    placeholder="Curso de Formação"
                                    value="{{ old('training_course', $evaluation->training_course) }}">
                                <label for="training_course">Curso de Formação</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="form-floating">
                                <input type="text" name="education_level" id="education_level" class="form-control"
                                    placeholder="Nível de Escolaridade"
                                    value="{{ old('education_level', $evaluation->education_level) }}">
                                <label for="education_level">Nível de Escolaridade</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="form-floating">
                                <input type="text" name="job_role" id="job_role" class="form-control"
                                    placeholder="Cargo/Função" value="{{ old('job_role', $evaluation->job_role) }}">
                                <label for="job_role">Cargo/Função</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text" name="experience_time" id="experience_time" class="form-control"
                                    placeholder="Tempo de Experiência"
                                    value="{{ old('experience_time', $evaluation->experience_time) }}">
                                <label for="experience_time">Tempo de Experiência</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="completed_workload">Cumpriu a Carga Horária?</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="completed_workload_switch"
                                        {{ old('completed_workload', strtolower(trim($evaluation->completed_workload ?? '')) === 'sim') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="completed_workload_switch">
                                        <span id="workload_status_text">
                                            {{ old('completed_workload', strtolower(trim($evaluation->completed_workload ?? '')) === 'sim') ? 'Sim' : 'Não' }}
                                        </span>
                                    </label>
                                </div>
                                <input type="hidden" name="completed_workload" id="completed_workload"
                                    value="{{ old('completed_workload', strtolower(trim($evaluation->completed_workload ?? '')) === 'sim' ? 'Sim' : 'Não') }}">
                                <small class="form-text text-muted">Marque se o estagiário cumpriu a carga horária</small>
                            </div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const switchElement = document.getElementById('completed_workload_switch');
                            const hiddenInput = document.getElementById('completed_workload');
                            const statusText = document.getElementById('workload_status_text');

                            switchElement.addEventListener('change', function() {
                                if (this.checked) {
                                    hiddenInput.value = 'Sim';
                                    statusText.textContent = 'Sim';
                                } else {
                                    hiddenInput.value = 'Não';
                                    statusText.textContent = 'Não';
                                }
                            });
                        });
                    </script>

                    {{-- Avaliações de Desempenho --}}
                    <h5 class="text-primary mb-3 mt-4">Avaliações de Desempenho</h5>
                    <p class="text-muted small mb-3">
                        Escala de avaliação: <strong>Ótimo (2.0)</strong> | <strong>Muito bom (1.5)</strong> | <strong>Bom
                            (1.0)</strong> | <strong>Satisfatório (0.5)</strong> | <strong>Insatisfatório (0.0)</strong>
                    </p>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <select name="performance" id="performance" class="form-select">
                                    <option value="">Selecione...</option>
                                    <option value="Ótimo"
                                        {{ old('performance', $evaluation->performance) === 'Ótimo' ? 'selected' : '' }}>
                                        Ótimo (2.0)</option>
                                    <option value="Muito Bom"
                                        {{ old('performance', $evaluation->performance) === 'Muito Bom' ? 'selected' : '' }}>
                                        Muito Bom (1.5)</option>
                                    <option value="Bom"
                                        {{ old('performance', $evaluation->performance) === 'Bom' ? 'selected' : '' }}>
                                        Bom (1.0)</option>
                                    <option value="Satisfatório"
                                        {{ old('performance', $evaluation->performance) === 'Satisfatório' ? 'selected' : '' }}>
                                        Satisfatório (0.5)</option>
                                    <option value="Insatisfatório"
                                        {{ old('performance', $evaluation->performance) === 'Insatisfatório' ? 'selected' : '' }}>
                                        Insatisfatório (0.0)</option>
                                </select>
                                <label for="performance">1. Rendimento</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <select name="comprehension" id="comprehension" class="form-select">
                                    <option value="">Selecione...</option>
                                    <option value="Ótimo"
                                        {{ old('comprehension', $evaluation->comprehension) === 'Ótimo' ? 'selected' : '' }}>
                                        Ótimo (2.0)</option>
                                    <option value="Muito Bom"
                                        {{ old('comprehension', $evaluation->comprehension) === 'Muito Bom' ? 'selected' : '' }}>
                                        Muito Bom (1.5)</option>
                                    <option value="Bom"
                                        {{ old('comprehension', $evaluation->comprehension) === 'Bom' ? 'selected' : '' }}>
                                        Bom (1.0)</option>
                                    <option value="Satisfatório"
                                        {{ old('comprehension', $evaluation->comprehension) === 'Satisfatório' ? 'selected' : '' }}>
                                        Satisfatório (0.5)</option>
                                    <option value="Insatisfatório"
                                        {{ old('comprehension', $evaluation->comprehension) === 'Insatisfatório' ? 'selected' : '' }}>
                                        Insatisfatório (0.0)</option>
                                </select>
                                <label for="comprehension">2. Facilidade de Compreensão</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <select name="technical_knowledge" id="technical_knowledge" class="form-select">
                                    <option value="">Selecione...</option>
                                    <option value="Ótimo"
                                        {{ old('technical_knowledge', $evaluation->technical_knowledge) === 'Ótimo' ? 'selected' : '' }}>
                                        Ótimo (2.0)</option>
                                    <option value="Muito Bom"
                                        {{ old('technical_knowledge', $evaluation->technical_knowledge) === 'Muito Bom' ? 'selected' : '' }}>
                                        Muito Bom (1.5)</option>
                                    <option value="Bom"
                                        {{ old('technical_knowledge', $evaluation->technical_knowledge) === 'Bom' ? 'selected' : '' }}>
                                        Bom (1.0)</option>
                                    <option value="Satisfatório"
                                        {{ old('technical_knowledge', $evaluation->technical_knowledge) === 'Satisfatório' ? 'selected' : '' }}>
                                        Satisfatório (0.5)</option>
                                    <option value="Insatisfatório"
                                        {{ old('technical_knowledge', $evaluation->technical_knowledge) === 'Insatisfatório' ? 'selected' : '' }}>
                                        Insatisfatório (0.0)</option>
                                </select>
                                <label for="technical_knowledge">3. Conhecimentos Técnicos</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <select name="organization" id="organization" class="form-select">
                                    <option value="">Selecione...</option>
                                    <option value="Ótimo"
                                        {{ old('organization', $evaluation->organization) === 'Ótimo' ? 'selected' : '' }}>
                                        Ótimo (2.0)</option>
                                    <option value="Muito Bom"
                                        {{ old('organization', $evaluation->organization) === 'Muito Bom' ? 'selected' : '' }}>
                                        Muito Bom (1.5)</option>
                                    <option value="Bom"
                                        {{ old('organization', $evaluation->organization) === 'Bom' ? 'selected' : '' }}>
                                        Bom (1.0)</option>
                                    <option value="Satisfatório"
                                        {{ old('organization', $evaluation->organization) === 'Satisfatório' ? 'selected' : '' }}>
                                        Satisfatório (0.5)</option>
                                    <option value="Insatisfatório"
                                        {{ old('organization', $evaluation->organization) === 'Insatisfatório' ? 'selected' : '' }}>
                                        Insatisfatório (0.0)</option>
                                </select>
                                <label for="organization">4. Organização</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <select name="initiative" id="initiative" class="form-select">
                                    <option value="">Selecione...</option>
                                    <option value="Ótimo"
                                        {{ old('initiative', $evaluation->initiative) === 'Ótimo' ? 'selected' : '' }}>
                                        Ótimo (2.0)</option>
                                    <option value="Muito Bom"
                                        {{ old('initiative', $evaluation->initiative) === 'Muito Bom' ? 'selected' : '' }}>
                                        Muito Bom (1.5)</option>
                                    <option value="Bom"
                                        {{ old('initiative', $evaluation->initiative) === 'Bom' ? 'selected' : '' }}>
                                        Bom (1.0)</option>
                                    <option value="Satisfatório"
                                        {{ old('initiative', $evaluation->initiative) === 'Satisfatório' ? 'selected' : '' }}>
                                        Satisfatório (0.5)</option>
                                    <option value="Insatisfatório"
                                        {{ old('initiative', $evaluation->initiative) === 'Insatisfatório' ? 'selected' : '' }}>
                                        Insatisfatório (0.0)</option>
                                </select>
                                <label for="initiative">5. Iniciativa</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <select name="attendance" id="attendance" class="form-select">
                                    <option value="">Selecione...</option>
                                    <option value="Ótimo"
                                        {{ old('attendance', $evaluation->attendance) === 'Ótimo' ? 'selected' : '' }}>
                                        Ótimo (2.0)</option>
                                    <option value="Muito Bom"
                                        {{ old('attendance', $evaluation->attendance) === 'Muito Bom' ? 'selected' : '' }}>
                                        Muito Bom (1.5)</option>
                                    <option value="Bom"
                                        {{ old('attendance', $evaluation->attendance) === 'Bom' ? 'selected' : '' }}>
                                        Bom (1.0)</option>
                                    <option value="Satisfatório"
                                        {{ old('attendance', $evaluation->attendance) === 'Satisfatório' ? 'selected' : '' }}>
                                        Satisfatório (0.5)</option>
                                    <option value="Insatisfatório"
                                        {{ old('attendance', $evaluation->attendance) === 'Insatisfatório' ? 'selected' : '' }}>
                                        Insatisfatório (0.0)</option>
                                </select>
                                <label for="attendance">6. Assiduidade</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <select name="discipline" id="discipline" class="form-select">
                                    <option value="">Selecione...</option>
                                    <option value="Ótimo"
                                        {{ old('discipline', $evaluation->discipline) === 'Ótimo' ? 'selected' : '' }}>
                                        Ótimo (2.0)</option>
                                    <option value="Muito Bom"
                                        {{ old('discipline', $evaluation->discipline) === 'Muito Bom' ? 'selected' : '' }}>
                                        Muito Bom (1.5)</option>
                                    <option value="Bom"
                                        {{ old('discipline', $evaluation->discipline) === 'Bom' ? 'selected' : '' }}>
                                        Bom (1.0)</option>
                                    <option value="Satisfatório"
                                        {{ old('discipline', $evaluation->discipline) === 'Satisfatório' ? 'selected' : '' }}>
                                        Satisfatório (0.5)</option>
                                    <option value="Insatisfatório"
                                        {{ old('discipline', $evaluation->discipline) === 'Insatisfatório' ? 'selected' : '' }}>
                                        Insatisfatório (0.0)</option>
                                </select>
                                <label for="discipline">7. Disciplina</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <select name="sociability" id="sociability" class="form-select">
                                    <option value="">Selecione...</option>
                                    <option value="Ótimo"
                                        {{ old('sociability', $evaluation->sociability) === 'Ótimo' ? 'selected' : '' }}>
                                        Ótimo (2.0)</option>
                                    <option value="Muito Bom"
                                        {{ old('sociability', $evaluation->sociability) === 'Muito Bom' ? 'selected' : '' }}>
                                        Muito Bom (1.5)</option>
                                    <option value="Bom"
                                        {{ old('sociability', $evaluation->sociability) === 'Bom' ? 'selected' : '' }}>
                                        Bom (1.0)</option>
                                    <option value="Satisfatório"
                                        {{ old('sociability', $evaluation->sociability) === 'Satisfatório' ? 'selected' : '' }}>
                                        Satisfatório (0.5)</option>
                                    <option value="Insatisfatório"
                                        {{ old('sociability', $evaluation->sociability) === 'Insatisfatório' ? 'selected' : '' }}>
                                        Insatisfatório (0.0)</option>
                                </select>
                                <label for="sociability">8. Sociabilidade</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <select name="cooperation" id="cooperation" class="form-select">
                                    <option value="">Selecione...</option>
                                    <option value="Ótimo"
                                        {{ old('cooperation', $evaluation->cooperation) === 'Ótimo' ? 'selected' : '' }}>
                                        Ótimo (2.0)</option>
                                    <option value="Muito Bom"
                                        {{ old('cooperation', $evaluation->cooperation) === 'Muito Bom' ? 'selected' : '' }}>
                                        Muito Bom (1.5)</option>
                                    <option value="Bom"
                                        {{ old('cooperation', $evaluation->cooperation) === 'Bom' ? 'selected' : '' }}>
                                        Bom (1.0)</option>
                                    <option value="Satisfatório"
                                        {{ old('cooperation', $evaluation->cooperation) === 'Satisfatório' ? 'selected' : '' }}>
                                        Satisfatório (0.5)</option>
                                    <option value="Insatisfatório"
                                        {{ old('cooperation', $evaluation->cooperation) === 'Insatisfatório' ? 'selected' : '' }}>
                                        Insatisfatório (0.0)</option>
                                </select>
                                <label for="cooperation">9. Cooperação</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <select name="responsibility" id="responsibility" class="form-select">
                                    <option value="">Selecione...</option>
                                    <option value="Ótimo"
                                        {{ old('responsibility', $evaluation->responsibility) === 'Ótimo' ? 'selected' : '' }}>
                                        Ótimo (2.0)</option>
                                    <option value="Muito Bom"
                                        {{ old('responsibility', $evaluation->responsibility) === 'Muito Bom' ? 'selected' : '' }}>
                                        Muito Bom (1.5)</option>
                                    <option value="Bom"
                                        {{ old('responsibility', $evaluation->responsibility) === 'Bom' ? 'selected' : '' }}>
                                        Bom (1.0)</option>
                                    <option value="Satisfatório"
                                        {{ old('responsibility', $evaluation->responsibility) === 'Satisfatório' ? 'selected' : '' }}>
                                        Satisfatório (0.5)</option>
                                    <option value="Insatisfatório"
                                        {{ old('responsibility', $evaluation->responsibility) === 'Insatisfatório' ? 'selected' : '' }}>
                                        Insatisfatório (0.0)</option>
                                </select>
                                <label for="responsibility">10. Responsabilidade</label>
                            </div>
                        </div>
                    </div>

                    {{-- Observações e Comentários --}}
                    <h5 class="text-primary mb-3 mt-4">Observações e Comentários</h5>
                    <div class="mb-3">
                        <div class="form-floating">
                            <textarea name="considerations" id="considerations" class="form-control" placeholder="Considerações"
                                style="height: 100px">{{ old('considerations', $evaluation->considerations) }}</textarea>
                            <label for="considerations">Considerações</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-floating">
                            <textarea name="suggestions_to_institution" id="suggestions_to_institution" class="form-control"
                                placeholder="Sugestões à Instituição" style="height: 100px">{{ old('suggestions_to_institution', $evaluation->suggestions_to_institution) }}</textarea>
                            <label for="suggestions_to_institution">Sugestões à Instituição</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-floating">
                            <textarea name="performance_issues" id="performance_issues" class="form-control"
                                placeholder="Aspectos que Prejudicaram o Desempenho" style="height: 100px">{{ old('performance_issues', $evaluation->performance_issues) }}</textarea>
                            <label for="performance_issues">Aspectos que Prejudicaram o Desempenho</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-floating">
                            <textarea name="other_observations" id="other_observations" class="form-control" placeholder="Outras Observações"
                                style="height: 100px">{{ old('other_observations', $evaluation->other_observations) }}</textarea>
                            <label for="other_observations">Outras Observações</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal de Confirmação de Associação --}}
    <div class="modal fade" id="associateModal" tabindex="-1" aria-labelledby="associateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="associateModalLabel">
                        <i class="fas fa-link"></i> Confirmar Associação
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Tem certeza que deseja associar esta avaliação ao estágio selecionado?</p>
                    <div class="alert alert-warning mb-0" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Atenção:</strong> Esta ação não pode ser desfeita facilmente. A avaliação será arquivada e
                        vinculada permanentemente ao estágio.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-success"
                        onclick="document.getElementById('associateForm').submit();">
                        <i class="fas fa-check"></i> Confirmar Associação
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Confirmação de Exclusão da Avaliação --}}
    <div class="modal fade" id="deleteEvaluationModal" tabindex="-1" aria-labelledby="deleteEvaluationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteEvaluationModalLabel">
                        <i class="fas fa-trash"></i> Confirmar Exclusão
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja excluir esta avaliação? Esta ação pode ser desfeita posteriormente.
                </div>
                <div class="modal-footer">
                    <form action="{{ route('admin.supervisor-evaluations.destroy', $evaluation) }}" method="POST"
                        class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Confirmar Exclusão
                        </button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
@endsection
