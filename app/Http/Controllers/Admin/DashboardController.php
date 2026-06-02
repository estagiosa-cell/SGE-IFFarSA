<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InternshipStatus;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Course;
use App\Models\Internship;
use App\Models\SupervisorEvaluation;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * Controlador para exibir o painel principal (dashboard) da área administrativa.
 *
 * Este controlador é "invokable" e sua única responsabilidade é coletar
 * diversas estatísticas do sistema e exibi-las na view do dashboard.
 */
class DashboardController extends Controller
{
    use AuthorizesRequests;

    /**
     * Coleta dados e estatísticas e exibe o dashboard administrativo.
     *
     * @param  \Illuminate\Http\Request  $request  A requisição HTTP.
     * @return \Illuminate\View\View
     */
    public function __invoke(Request $request)
    {
        $this->authorize('viewAny', User::class);

        // Coleta estatísticas de estágios com uma única query agrupada por status.
        $statusCounts = Internship::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalInternships = $statusCounts->sum();
        $deletedInternships = Internship::onlyTrashed()->count();
        $internshipsByStatus = [
            'pending' => $statusCounts->get(InternshipStatus::PENDING->value, 0),
            'awaiting_signature' => $statusCounts->get(InternshipStatus::AWAITING_SIGNATURE->value, 0),
            'released' => $statusCounts->get(InternshipStatus::RELEASED->value, 0),
            'in_progress' => $statusCounts->get(InternshipStatus::IN_PROGRESS->value, 0),
            'completed' => $statusCounts->get(InternshipStatus::COMPLETED->value, 0),
            'cancelled' => $statusCounts->get(InternshipStatus::CANCELLED->value, 0),
        ];

        // Coleta estatísticas sobre as empresas (partes concedentes).
        $totalCompanies = Company::count();
        $deletedCompanies = Company::onlyTrashed()->count();
        // Conta quantas empresas únicas possuem estágios em andamento ou liberados.
        $activeCompanies = Internship::whereIn('status', [InternshipStatus::IN_PROGRESS->value, InternshipStatus::RELEASED->value])
            ->distinct('company_legal_identifier')
            ->count('company_legal_identifier');

        // Coleta estatísticas sobre os usuários do sistema com uma única query.
        $usersByRole = User::selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();
        $totalUsers = array_sum($usersByRole);
        $deletedUsers = User::onlyTrashed()->count();

        // Coleta estatísticas sobre os cursos.
        $totalCourses = Course::count();
        $deletedCourses = Course::onlyTrashed()->count();

        // Coleta estatísticas sobre as avaliações de supervisor.
        $totalEvaluations = SupervisorEvaluation::count();
        $deletedEvaluations = SupervisorEvaluation::onlyTrashed()->count();

        // Busca os estágios pendentes
        $pendingInternships = Internship::with(['advisor', 'course'])
            ->where('status', InternshipStatus::PENDING->value)
            ->latest()
            ->get();

        // Busca até 10 estágios que não estão pendentes
        $otherInternships = Internship::with(['advisor', 'course'])
            ->where('status', '!=', InternshipStatus::PENDING->value)
            ->orderByRaw(InternshipStatus::orderSql())
            ->latest()
            ->take(10)
            ->get();

        // Combina as listas garantindo que todos os pendentes fiquem no topo (primeiro) seguidos pelos demais
        $recentInternships = $pendingInternships->concat($otherInternships);

        // Retorna a view do dashboard com todas as estatísticas coletadas.
        return view('admin.dashboard', compact(
            'totalInternships',
            'deletedInternships',
            'internshipsByStatus',
            'totalCompanies',
            'deletedCompanies',
            'activeCompanies',
            'totalUsers',
            'deletedUsers',
            'usersByRole',
            'totalCourses',
            'deletedCourses',
            'totalEvaluations',
            'deletedEvaluations',
            'recentInternships'
        ));
    }
}
