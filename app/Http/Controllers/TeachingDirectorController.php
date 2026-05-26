<?php

namespace App\Http\Controllers;

use App\Enums\InternshipStatus;
use App\Models\Course;
use App\Models\Internship;
use App\Models\User;
use App\Utils\SearchHelper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * Controlador para visualização de estágios pela Direção de Ensino.
 *
 * Este controlador lida com a listagem de estágios de todos os estudantes,
 * com foco no status e período de realização, sem informações detalhadas
 * sobre responsáveis, parte concedente, supervisor e avaliações.
 */
class TeachingDirectorController extends Controller
{
    use AuthorizesRequests;

    /**
     * Exibe uma lista de estágios com filtros avançados.
     *
     * A Direção de Ensino pode visualizar todos os estágios do sistema
     * com filtros por curso, orientador, status, nome e período.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Internship::class);

        // Parâmetros de filtro
        $search = $request->get('search');
        $status = $request->get('status');
        $courseId = $request->get('course_id');
        $advisorId = $request->get('advisor_id');
        $registration = $request->get('registration');
        $endDateFrom = $request->get('end_date_from');
        $endDateTo = $request->get('end_date_to');
        $orderBy = $request->get('order_by', 'status_priority');

        // Inicia a query com os relacionamentos necessários
        $query = Internship::with(['advisor', 'course']);

        // Aplica filtro por status do estágio
        if ($request->filled('status')) {
            $query->where('status', $status);
        }

        // Aplica filtro por curso
        if ($request->filled('course_id')) {
            $query->where('course_id', $courseId);
        }

        // Aplica filtro por orientador
        if ($request->filled('advisor_id')) {
            $query->where('advisor_id', $advisorId);
        }

        // Aplica filtro por matrícula
        if ($request->filled('registration')) {
            $query->where('student_registration_number', 'like', '%'.$registration.'%');
        }

        // Aplica filtro por data de término (de)
        if ($request->filled('end_date_from')) {
            $query->whereDate('end_date', '>=', $endDateFrom);
        }

        // Aplica filtro por data de término (até)
        if ($request->filled('end_date_to')) {
            $query->whereDate('end_date', '<=', $endDateTo);
        }

        // Define ordenação por prioridade de status gerado pelo Enum
        $statusOrderSql = InternshipStatus::orderSql();

        // Aplica ordenação baseada no parâmetro
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

        $internships = SearchHelper::searchAndPaginate(
            $query,
            $request,
            $search,
            'student_name'
        );

        // Obtém opções para os filtros
        $statusOptions = InternshipStatus::options();
        $courses = Course::orderBy('name')->get(['id', 'name']);
        $advisors = User::whereIn('role', ['orientador', 'coordenador'])
            ->whereNull('deactivated_at')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('teaching-director.index', compact(
            'internships',
            'statusOptions',
            'courses',
            'advisors',
            'search',
            'status',
            'courseId',
            'advisorId',
            'registration',
            'endDateFrom',
            'endDateTo',
            'orderBy'
        ));
    }

    /**
     * Exibe os detalhes simplificados de um estágio.
     *
     * Mostra apenas informações do estudante, status e período,
     * sem dados de responsável, parte concedente, supervisor e avaliações.
     *
     * @param  \App\Models\Internship  $internship  O estágio a ser exibido.
     * @return \Illuminate\View\View
     */
    public function show(Internship $internship)
    {
        $this->authorize('view', $internship);

        // Carrega relacionamentos básicos
        $internship->load(['advisor', 'course']);

        return view('teaching-director.show', compact('internship'));
    }
}
