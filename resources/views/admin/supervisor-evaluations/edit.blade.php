@extends('layouts.auth')

@section('title', 'Gerenciar Avaliação')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Gerenciar Avaliação #{{ $evaluation->id }}</h2>
            <a href="{{ route('admin.supervisor-evaluations.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left-circle me-2"></i>Voltar
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
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white py-2">
                <h6 class="m-0 fw-bold">
                    <i class="bi bi-link-45deg"></i> Associar ao Estágio
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
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white py-2">
                <h6 class="m-0 fw-bold">
                    <i class="bi bi-pencil-square"></i> Editar Dados da Avaliação
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.supervisor-evaluations.update', $evaluation) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Informações Básicas --}}
                    <h5 class="text-primary mb-3">Informações Básicas</h5>
                    <div class="row m-0 m-0">
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
                    <div class="row m-0 m-0">
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

                    <div class="row m-0 m-0">
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

                    {{-- Critérios de Avaliação --}}
                    <h5 class="text-primary mb-3">Critérios de Avaliação</h5>
                    @php
                        $criteria = [
                            'performance' => '1. Desempenho',
                            'comprehension' => '2. Compreensão',
                            'technical_knowledge' => '3. Conhecimento Técnico',
                            'organization' => '4. Organização',
                            'initiative' => '5. Iniciativa',
                            'attendance' => '6. Assiduidade',
                            'discipline' => '7. Disciplina',
                            'sociability' => '8. Sociabilidade',
                            'cooperation' => '9. Cooperação',
                            'responsibility' => '10. Responsabilidade',
                        ];
                        $options = ['Ótimo', 'Muito Bom', 'Bom', 'Satisfatório', 'Insatisfatório'];
                    @endphp

                    <div class="row m-0 m-0">
                        @php
                            $chunks = array_chunk($criteria, 5, true);
                        @endphp

                        @foreach ($chunks as $chunk)
                            <div class="col-md-6">
                                <div class="row m-0 m-0">
                                    @foreach ($chunk as $field => $label)
                                        <div class="col-md-12 mb-3">
                                            <div class="form-floating">
                                                <select class="form-select @error($field) is-invalid @enderror"
                                                    id="{{ $field }}" name="{{ $field }}">
                                                    <option value="">Selecione uma opção</option>
                                                    @foreach ($options as $option)
                                                        <option value="{{ $option }}"
                                                            {{ old($field, $evaluation->$field) == $option ? 'selected' : '' }}>
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
