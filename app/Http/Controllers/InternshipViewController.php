<?php

namespace App\Http\Controllers;

use App\Enums\InternshipStatus;
use App\Models\Internship;
use App\Models\User;
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
        $orderBy = $request->get('order_by', 'status_priority'); // Padrão: ordenação por prioridade de status

        // Raw SQL para ordenação por prioridade de status gerado pelo Enum.
        // Garante que estágios 'Pendente' e 'Aguardando Assinatura' apareçam primeiro.
        $statusOrderSql = InternshipStatus::orderSql();

        if ($user->can('is-orientador')) {
            // Orientadores veem apenas os estágios que eles orientam.
            $query = $user->advisedInternships();

            // Aplica os filtros padrão da requisição na query.
            $query->applyStandardFilters($request);

            // Aplica ordenação
            $query = $query->with(['course', 'advisor']);
            $this->applyOrdering($query, $orderBy, $statusOrderSql);

            $internships = $query->paginate(100);
        } elseif ($user->can('is-coordenador')) {
            // Coordenadores veem estágios dos cursos que coordenam ou que eles próprios orientam.
            $courseIds = $user->coordinatedCourses()->pluck('id');

            $query = Internship::where(function ($q) use ($courseIds, $user) {
                $q->whereIn('course_id', $courseIds)
                    ->orWhere('advisor_id', $user->id);
            });

            // Aplica os filtros padrão da requisição na query.
            $query->applyStandardFilters($request, [
                'allow_advisor_filter' => true,
            ]);

            // Aplica ordenação
            $query = $query->with(['course', 'advisor']);
            $this->applyOrdering($query, $orderBy, $statusOrderSql);

            $internships = $query->paginate(100);

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

        return view('internship-view.index', compact('internships', 'advisors', 'statusOptions', 'orderBy'));
    }

    /**
     * Aplica a ordenação na query de estágios.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query  A query de estágios a ser ordenada.
     * @param  string  $orderBy  O tipo de ordenação a ser aplicada.
     * @param  string  $statusOrderSql  SQL para ordenação por prioridade de status.
     * @return \Illuminate\Database\Eloquent\Builder A query com a ordenação aplicada.
     */
    private function applyOrdering($query, $orderBy, $statusOrderSql)
    {
        switch ($orderBy) {
            case 'name_asc':
                $query->orderBy('student_name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('student_name', 'desc');
                break;
            case 'start_date_asc':
                $query->orderBy('start_date', 'asc');
                break;
            case 'start_date_desc':
                $query->orderBy('start_date', 'desc');
                break;
            case 'end_date_asc':
                $query->orderBy('end_date', 'asc');
                break;
            case 'end_date_desc':
                $query->orderBy('end_date', 'desc');
                break;
            case 'status_priority':
            default:
                $query->orderByRaw($statusOrderSql)
                    ->latest('end_date')
                    ->latest('updated_at');
                break;
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
