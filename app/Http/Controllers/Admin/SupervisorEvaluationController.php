<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InternshipStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssociateSupervisorEvaluationRequest;
use App\Http\Requests\UpdateSupervisorEvaluationRequest;
use App\Mail\AvaliacaoEstagioConcluida;
use App\Models\Internship;
use App\Models\SupervisorEvaluation;
use App\Utils\SearchHelper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Controlador para gerenciar as avaliações de estágio enviadas pelos supervisores.
 *
 * Este controlador lida com o fluxo de avaliações que são inicialmente armazenadas
 * em uma tabela temporária (`supervisor_evaluations`). O administrador pode então
 * revisar, editar e associar essas avaliações a um estágio em andamento,
 * transferindo os dados e, se aplicável, finalizando o estágio.
 */
class SupervisorEvaluationController extends Controller
{
    use AuthorizesRequests;

    /**
     * Exibe a lista de avaliações de supervisores com filtros e paginação.
     *
     * @param  \Illuminate\Http\Request  $request  A requisição HTTP, contendo possíveis filtros.
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', SupervisorEvaluation::class);

        $query = SupervisorEvaluation::query();

        // Filtro para exibir registros que foram soft-deletados.
        $showDeleted = $request->input('show_deleted') === '1';
        if ($showDeleted) {
            $query->onlyTrashed();
        }

        // Filtro para verificar se a carga horária foi cumprida.
        if ($request->filled('workload')) {
            if ($request->workload === 'completed') {
                // Filtra por avaliações onde a carga horária foi 'sim'.
                $query->whereRaw('LOWER(completed_workload) = ?', ['sim']);
            } elseif ($request->workload === 'not_completed') {
                // Filtra por avaliações onde a carga horária não foi 'sim' ou é nula.
                $query->where(function ($q) {
                    $q->whereRaw('LOWER(completed_workload) != ?', ['sim'])
                        ->orWhereNull('completed_workload');
                });
            }
        }

        $orderedQuery = $query->orderBy('created_at', 'desc');
        $evaluations = SearchHelper::searchAndPaginate(
            $orderedQuery,
            $request,
            $request->input('search'),
            'student_name'
        );

        $activeFiltersCount = collect([
            $request->input('search'),
            $request->input('workload'),
        ])->filter(fn ($v) => filled($v))->count();

        return view('admin.supervisor-evaluations.index', compact('evaluations', 'activeFiltersCount'));
    }

    /**
     * Exibe o formulário para editar uma avaliação e associá-la a um estágio.
     *
     * @param  \App\Models\SupervisorEvaluation  $evaluation  A avaliação a ser editada.
     * @return \Illuminate\View\View
     */
    public function edit(SupervisorEvaluation $evaluation)
    {
        $this->authorize('update', $evaluation);

        // Busca estágios com status "Em Andamento" para o dropdown de associação.
        $internships = Internship::select(['id', 'student_name', 'course_id'])
            ->where('status', InternshipStatus::IN_PROGRESS)
            ->with('course:id,name')
            ->orderBy('student_name')
            ->get();

        return view('admin.supervisor-evaluations.edit', compact('evaluation', 'internships'));
    }

    /**
     * Atualiza os dados de uma avaliação de supervisor.
     *
     * @param  \App\Http\Requests\UpdateSupervisorEvaluationRequest  $request  A requisição HTTP com os dados da avaliação.
     * @param  \App\Models\SupervisorEvaluation  $evaluation  A avaliação a ser atualizada.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateSupervisorEvaluationRequest $request, SupervisorEvaluation $evaluation)
    {
        $validated = $request->validated();

        // Atualiza a avaliação com os dados validados.
        $evaluation->update($validated);

        return redirect()
            ->route('admin.supervisor-evaluations.edit', $evaluation)
            ->with('message', 'Avaliação atualizada com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Associa uma avaliação de supervisor a um estágio, copia os dados e finaliza o processo.
     *
     * @param  \App\Http\Requests\AssociateSupervisorEvaluationRequest  $request  A requisição HTTP contendo o ID do estágio.
     * @param  \App\Models\SupervisorEvaluation  $evaluation  A avaliação a ser associada.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function associate(AssociateSupervisorEvaluationRequest $request, SupervisorEvaluation $evaluation)
    {
        $validated = $request->validated();

        $internship = Internship::findOrFail($validated['internship_id']);

        // Garante que a associação só ocorra para estágios em andamento.
        if ($internship->status !== InternshipStatus::IN_PROGRESS) {
            return redirect()
                ->back()
                ->with('message', 'Só é possível associar avaliações a estágios em andamento.')
                ->with('messageType', 'danger');
        }

        // Inicia uma transação para garantir a integridade dos dados.
        DB::beginTransaction();
        try {
            $evaluationData = [
                'evaluation_has_academic_background' => $evaluation->has_academic_background,
                'evaluation_completed_workload' => $evaluation->completed_workload,
                'evaluation_training_course' => $evaluation->training_course,
                'evaluation_education_level' => $evaluation->education_level,
                'evaluation_job_role' => $evaluation->job_role,
                'evaluation_experience_time' => $evaluation->experience_time,
                'evaluation_performance' => $evaluation->performance,
                'evaluation_comprehension' => $evaluation->comprehension,
                'evaluation_technical_knowledge' => $evaluation->technical_knowledge,
                'evaluation_organization' => $evaluation->organization,
                'evaluation_initiative' => $evaluation->initiative,
                'evaluation_attendance' => $evaluation->attendance,
                'evaluation_discipline' => $evaluation->discipline,
                'evaluation_sociability' => $evaluation->sociability,
                'evaluation_cooperation' => $evaluation->cooperation,
                'evaluation_responsibility' => $evaluation->responsibility,
                'evaluation_considerations' => $evaluation->considerations,
                'evaluation_suggestions_to_institution' => $evaluation->suggestions_to_institution,
                'evaluation_performance_issues' => $evaluation->performance_issues,
                'evaluation_other_observations' => $evaluation->other_observations,
            ];

            // Calcula a nota final da avaliação usando as lógicas de peso próprias do estágio
            $evaluationData['evaluation_grade'] = $internship->calculateEvaluationGrade($evaluationData);

            // Copia todos os dados da avaliação para os campos correspondentes no estágio.
            $internship->update($evaluationData);

            // Se o supervisor confirmou que a carga horária foi cumprida, o estágio é finalizado.
            if ($evaluation->hasCompletedWorkload()) {
                $internship->update([
                    'status' => InternshipStatus::COMPLETED,
                ]);

                // Envia e-mail ao estagiário informando a conclusão da avaliação e a nota atribuída.
                if (!empty($internship->student_email)) {
                    $internship->load('course:id,name');
                    Mail::to($internship->student_email)->send(new AvaliacaoEstagioConcluida($internship));
                }
            }

            // Realiza o soft delete da avaliação temporária para removê-la da lista de pendências.
            $evaluation->delete();

            // Confirma as alterações no banco de dados.
            DB::commit();

            return redirect()
                ->route('admin.supervisor-evaluations.index')
                ->with('message', 'Avaliação associada ao estágio com sucesso!')
                ->with('messageType', 'success');
        } catch (\Exception $e) {
            // Em caso de erro, reverte todas as operações.
            DB::rollBack();

            return redirect()
                ->back()
                ->with('message', 'Erro ao associar avaliação: '.$e->getMessage())
                ->with('messageType', 'danger');
        }
    }

    /**
     * Remove a avaliação especificada (soft delete).
     *
     * @param  \App\Models\SupervisorEvaluation  $evaluation  A avaliação a ser excluída.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(SupervisorEvaluation $evaluation)
    {
        $this->authorize('delete', $evaluation);

        $evaluation->delete();

        return redirect()
            ->route('admin.supervisor-evaluations.index')
            ->with('message', 'Avaliação excluída com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Restaura uma avaliação que foi excluída (soft deleted).
     *
     * @param  int  $id  O ID da avaliação a ser restaurada.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($id)
    {
        // Busca a avaliação na lixeira ou falha.
        $evaluation = SupervisorEvaluation::withTrashed()->findOrFail($id);

        $this->authorize('restore', $evaluation);

        // Restaura o registro.
        $evaluation->restore();

        return redirect()
            ->route('admin.supervisor-evaluations.index')
            ->with('message', 'Avaliação restaurada com sucesso!')
            ->with('messageType', 'success');
    }
}
