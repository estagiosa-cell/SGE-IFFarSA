<?php

namespace App\Http\Controllers;

use App\Enums\InternshipStatus;
use App\Models\Course;
use App\Models\Internship;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
     * @return View
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
        $query = Internship::select([
            'id', 'student_name', 'student_email', 'student_registration_number',
            'status', 'start_date', 'end_date',
            'advisor_id', 'course_id', 'company_name',
            'company_legal_identifier', 'deleted_at', 'updated_at', 'internship_type_name',
        ])
            ->with(['advisor:id,name', 'course:id,name']);

        // Aplica os filtros padrão da requisição
        $query->applyStandardFilters($request, [
            'allow_advisor_filter' => true,
            'allow_course_filter' => true,
            'skip_name_search' => true,
        ]);

        // Aplica ordenação baseada no parâmetro
        $query->applyStandardOrdering($orderBy);

        $internships = $query->search($search, ['student_name'])
            ->paginate(100)
            ->withQueryString();

        // Obtém opções para os filtros
        $statusOptions = InternshipStatus::options();
        $courses = Course::orderBy('name')->get(['id', 'name']);
        $advisors = User::whereIn('role', ['orientador', 'coordenador'])
            ->whereNull('deactivated_at')
            ->orderBy('name')
            ->get(['id', 'name']);

        $activeFilters = collect([
            $search,
            $status,
            $courseId,
            $advisorId,
            $registration,
            $endDateFrom,
            $endDateTo,
        ]);
        if ($orderBy && $orderBy !== 'status_priority') {
            $activeFilters->push($orderBy);
        }
        $activeFiltersCount = $activeFilters->filter(fn ($v) => filled($v))->count();

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
            'orderBy',
            'activeFiltersCount'
        ));

    }

    /**
     * Exibe os detalhes simplificados de um estágio.
     *
     * Mostra apenas informações do estudante, status e período,
     * sem dados de responsável, parte concedente, supervisor e avaliações.
     *
     * @param  Internship  $internship  O estágio a ser exibido.
     * @return View
     */
    public function show($id)
    {
        $internship = Internship::select([
            'id', 'student_name', 'student_registration_number', 'student_email', 'student_year_semester',
            'status', 'start_date', 'end_date', 'required_hours', 'internship_type_name',
            'advisor_id', 'course_id', 'company_name', 'company_address_city',
        ])
            ->with(['advisor:id,name', 'course:id,name'])
            ->findOrFail($id);

        $this->authorize('view', $internship);

        return view('teaching-director.show', compact('internship'));
    }
}
