<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InternshipStatus;
use App\Http\Controllers\Controller;
use App\Models\Internship;
use App\Models\SupervisorEvaluation;
use App\Utils\SearchHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupervisorEvaluationController extends Controller
{
    /**
     * Lista todas as avaliações (ativas ou deletadas)
     */
    public function index(Request $request)
    {
        $query = SupervisorEvaluation::query();

        // Filtro para mostrar deletadas
        $showDeleted = $request->input('show_deleted') === '1';
        if ($showDeleted) {
            $query->onlyTrashed();
        }

        // Filtro de busca com SearchHelper (ignora acentos e busca por palavras)
        if ($request->filled('search')) {
            $search = $request->search;
            SearchHelper::searchInFields($query, $search, [
                'student_name',
            ]);
        }

        // Filtro de carga horária
        if ($request->filled('workload')) {
            if ($request->workload === 'completed') {
                $query->whereRaw('LOWER(completed_workload) = ?', ['sim']);
            } elseif ($request->workload === 'not_completed') {
                $query->where(function ($q) {
                    $q->whereRaw('LOWER(completed_workload) != ?', ['sim'])
                        ->orWhereNull('completed_workload');
                });
            }
        }

        $evaluations = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.supervisor-evaluations.index', compact('evaluations'));
    }

    /**
     * Exibe o formulário de edição/associação
     */
    public function edit(SupervisorEvaluation $evaluation)
    {
        // Busca estágios em andamento para possível associação
        $internships = Internship::where('status', InternshipStatus::IN_PROGRESS)
            ->with('course')
            ->orderBy('student_name')
            ->get();

        return view('admin.supervisor-evaluations.edit', compact('evaluation', 'internships'));
    }

    /**
     * Atualiza os dados da avaliação
     */
    public function update(Request $request, SupervisorEvaluation $evaluation)
    {
        $validated = $request->validate([
            'supervisor_email' => 'nullable|email|max:255',
            'student_name' => 'nullable|string|max:255',
            'supervisor_name' => 'nullable|string|max:255',
            'has_academic_background' => 'nullable|string|max:255',
            'completed_workload' => 'nullable|string|max:255',
            'training_course' => 'nullable|string|max:255',
            'education_level' => 'nullable|string|max:255',
            'job_role' => 'nullable|string|max:255',
            'experience_time' => 'nullable|string|max:255',
            'performance' => 'nullable|string|max:255',
            'comprehension' => 'nullable|string|max:255',
            'technical_knowledge' => 'nullable|string|max:255',
            'organization' => 'nullable|string|max:255',
            'initiative' => 'nullable|string|max:255',
            'attendance' => 'nullable|string|max:255',
            'discipline' => 'nullable|string|max:255',
            'sociability' => 'nullable|string|max:255',
            'cooperation' => 'nullable|string|max:255',
            'responsibility' => 'nullable|string|max:255',
            'considerations' => 'nullable|string',
            'suggestions_to_institution' => 'nullable|string',
            'performance_issues' => 'nullable|string',
            'other_observations' => 'nullable|string',
        ]);

        $evaluation->update($validated);

        return redirect()
            ->route('admin.supervisor-evaluations.edit', $evaluation)
            ->with('message', 'Avaliação atualizada com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Associa a avaliação a um estágio e copia os dados
     */
    public function associate(Request $request, SupervisorEvaluation $evaluation)
    {
        $request->validate([
            'internship_id' => 'required|exists:internships,id',
        ]);

        $internship = Internship::findOrFail($request->internship_id);

        // Verifica se o estágio está em andamento
        if ($internship->status !== InternshipStatus::IN_PROGRESS) {
            return redirect()
                ->back()
                ->with('message', 'Só é possível associar avaliações a estágios em andamento.')
                ->with('messageType', 'danger');
        }

        DB::beginTransaction();
        try {
            // Calcula a nota total da avaliação
            $evaluationGrade = $evaluation->calculateGrade();

            // Copia os dados da avaliação para o estágio
            $internship->update([
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
                'evaluation_grade' => $evaluationGrade,
            ]);

            // Se o supervisor confirmou que a carga horária foi cumprida, atualiza o status
            if ($evaluation->hasCompletedWorkload()) {
                $internship->update([
                    'status' => InternshipStatus::COMPLETED,
                ]);
            }

            // Soft delete na avaliação temporária para remover da lista
            $evaluation->delete();

            DB::commit();

            return redirect()
                ->route('admin.supervisor-evaluations.index')
                ->with('message', 'Avaliação associada ao estágio com sucesso!')
                ->with('messageType', 'success');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('message', 'Erro ao associar avaliação: '.$e->getMessage())
                ->with('messageType', 'danger');
        }
    }

    /**
     * Soft delete da avaliação
     */
    public function destroy(SupervisorEvaluation $evaluation)
    {
        $evaluation->delete();

        return redirect()
            ->route('admin.supervisor-evaluations.index')
            ->with('message', 'Avaliação excluída com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Restaura uma avaliação soft deleted
     */
    public function restore($id)
    {
        $evaluation = SupervisorEvaluation::withTrashed()->findOrFail($id);

        $evaluation->restore();

        return redirect()
            ->route('admin.supervisor-evaluations.index')
            ->with('message', 'Avaliação restaurada com sucesso!')
            ->with('messageType', 'success');
    }
}
