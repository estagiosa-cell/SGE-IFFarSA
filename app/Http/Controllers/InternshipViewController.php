<?php

namespace App\Http\Controllers;

use App\Enums\InternshipStatus;
use App\Models\Course;
use App\Models\Internship;
use App\Models\User;
use App\Utils\SearchHelper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador para visualização de estágios por parte de orientadores e coordenadores.
 *
 * Este controlador lida com a listagem e visualização de estágios,
 * aplicando filtros e regras de permissão com base no perfil do usuário logado.
 */
class InternshipViewController extends Controller
{
    use AuthorizesRequests;

    /**
     * Exibe uma lista de estágios com base no perfil do usuário (orientador ou coordenador).
     *
     * Aplica filtros de pesquisa, status, orientador e matrícula.
     * A ordenação prioriza os status que requerem mais atenção.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Internship::class);

        $user = Auth::user();
        $advisors = collect();
        $courses = collect();
        $statusOptions = InternshipStatus::options();
        $orderBy = $request->get('order_by', 'status_priority'); // Padrão: ordenação por prioridade de status



        if ($user->can('is-orientador')) {
            // Orientadores veem apenas os estágios que eles orientam.
            $query = $user->advisedInternships();

            // Aplica os filtros padrão da requisição na query.
            $query->applyStandardFilters($request, [
                'skip_name_search' => true,
            ]);

            // Aplica ordenação
            $query = $query->select([
                    'id', 'student_name', 'student_registration_number',
                    'status', 'start_date', 'end_date',
                    'advisor_id', 'course_id', 'company_name',
                    'company_legal_identifier', 'updated_at',
                ])
                ->with(['course:id,name', 'advisor:id,name']);
            $query->applyStandardOrdering($orderBy);

            $internships = SearchHelper::searchAndPaginate(
                $query,
                $request,
                $request->input('search'),
                'student_name'
            );
        } elseif ($user->can('is-coordenador')) {
            // Coordenadores veem estágios dos cursos que coordenam ou que eles próprios orientam.
            $courses = Course::where('coordinator_id', $user->id)
                ->orWhere('secondary_coordinator_id', $user->id)
                ->orderBy('name')
                ->get();
            $courseIds = $courses->pluck('id');

            $query = Internship::where(function ($q) use ($courseIds, $user) {
                $q->whereIn('course_id', $courseIds)
                    ->orWhere('advisor_id', $user->id);
            });

            // Aplica os filtros padrão da requisição na query.
            $query->applyStandardFilters($request, [
                'allow_advisor_filter' => true,
                'allow_course_filter' => true,
                'skip_name_search' => true,
            ]);

            // Aplica ordenação
            $query = $query->select([
                    'id', 'student_name', 'student_registration_number',
                    'status', 'start_date', 'end_date',
                    'advisor_id', 'course_id', 'company_name',
                    'company_legal_identifier', 'updated_at',
                ])
                ->with(['course:id,name', 'advisor:id,name']);
            $query->applyStandardOrdering($orderBy);

            $internships = SearchHelper::searchAndPaginate(
                $query,
                $request,
                $request->input('search'),
                'student_name'
            );

            // Busca orientadores que orientam estágios dos cursos coordenados para popular o filtro.
            $advisorIds = Internship::whereIn('course_id', $courseIds)
                ->distinct()
                ->pluck('advisor_id');

            $advisors = User::whereIn('id', $advisorIds)
                ->whereIn('role', ['orientador', 'coordenador'])
                ->whereNull('deactivated_at')
                ->orderBy('name')
                ->get();
        } else {
            // Se o usuário não for orientador nem coordenador, bloqueia o acesso.
            abort(403);
        }

        $activeFilters = collect([
            request('search'),
            request('registration'),
            request('status'),
            request('end_date_from'),
            request('end_date_to'),
        ]);
        if (auth()->user()->can('is-coordenador')) {
            $activeFilters->push(request('advisor'));
            $activeFilters->push(request('course_id'));
        }
        if ($orderBy && $orderBy !== 'status_priority') {
            $activeFilters->push($orderBy);
        }
        $activeFiltersCount = $activeFilters->filter(fn ($v) => filled($v))->count();

        return view('internship-view.index', compact('internships', 'advisors', 'courses', 'statusOptions', 'orderBy', 'activeFiltersCount'));
    }



    /**
     * Exibe os detalhes de um estágio específico.
     *
     * Garante que o usuário (orientador ou coordenador) tenha permissão
     * para visualizar o estágio solicitado.
     *
     * @param  \App\Models\Internship  $internship  O estágio a ser exibido.
     * @return \Illuminate\View\View
     */
    public function show(Internship $internship)
    {
        $this->authorize('view', $internship);

        // Carrega os relacionamentos para evitar N+1 queries na view.
        $internship->load(['advisor:id,name', 'course:id,name']);

        return view('internship-view.show', compact('internship'));
    }
}
