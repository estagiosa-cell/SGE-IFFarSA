<?php

namespace App\Http\Controllers;

use App\Enums\InternshipStatus;
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
        $statusOptions = InternshipStatus::options();

        // Raw SQL para ordenação por prioridade de status.
        // Garante que estágios 'Pendente' e 'Aguardando Assinatura' apareçam primeiro.
        $statusOrderSql = "
            CASE status
                WHEN 'Pendente' THEN 1
                WHEN 'Aguardando Assinatura' THEN 2
                WHEN 'Em Andamento' THEN 3
                WHEN 'Concluído' THEN 4
                WHEN 'Cancelado' THEN 5
                ELSE 99
            END
        ";

        if ($user->can('is-orientador')) {
            // Orientadores veem apenas os estágios que eles orientam.
            $query = $user->advisedInternships();

            // Aplica os filtros da requisição na query.
            $this->applyFilters($query, $request);

            $internships = $query->with(['course', 'advisor'])
                ->orderByRaw($statusOrderSql)
                ->orderBy('updated_at', 'desc')
                ->paginate(100);
        } elseif ($user->can('is-coordenador')) {
            // Coordenadores veem estágios dos cursos que coordenam ou que eles próprios orientam.
            $courseIds = $user->coordinatedCourses()->pluck('id');

            $query = Internship::where(function ($q) use ($courseIds, $user) {
                $q->whereIn('course_id', $courseIds)
                    ->orWhere('advisor_id', $user->id);
            });

            // Aplica os filtros da requisição na query.
            $this->applyFilters($query, $request);

            $internships = $query->with(['course', 'advisor'])
                ->orderByRaw($statusOrderSql)
                ->latest('end_date')
                ->latest('updated_at')
                ->paginate(100);

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
            // Se o usuário não for orientador nem coordenador, retorna lista vazia
            $internships = Internship::query()->whereRaw('1 = 0')->paginate(100);
        }

        return view('internship-view.index', compact('internships', 'advisors', 'statusOptions'));
    }

    /**
     * Aplica os filtros da requisição na query de estágios.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query  A query de estágios a ser filtrada.
     * @param  \Illuminate\Http\Request  $request  A requisição HTTP com os parâmetros de filtro.
     * @return \Illuminate\Database\Eloquent\Builder A query com os filtros aplicados.
     */
    private function applyFilters($query, Request $request)
    {
        // Filtro por nome do estudante.
        if ($request->filled('search')) {
            SearchHelper::searchInField($query, $request->search, 'student_name');
        }

        // Filtro por status do estágio.
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por orientador (disponível apenas para coordenadores).
        if ($request->filled('advisor') && Auth::user()->can('is-coordenador')) {
            $query->where('advisor_id', $request->advisor);
        }

        // Filtro por número de matrícula do estudante.
        if ($request->filled('registration')) {
            $query->where('student_registration_number', 'like', '%'.$request->registration.'%');
        }

        // Filtro por data de término (de).
        if ($request->filled('end_date_from')) {
            $query->whereDate('end_date', '>=', $request->end_date_from);
        }

        // Filtro por data de término (até).
        if ($request->filled('end_date_to')) {
            $query->whereDate('end_date', '<=', $request->end_date_to);
        }

        return $query;
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
        $internship->load(['advisor', 'course']);

        return view('internship-view.show', compact('internship'));
    }
}
