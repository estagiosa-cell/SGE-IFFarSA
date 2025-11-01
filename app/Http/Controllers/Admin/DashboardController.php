<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InternshipStatus;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Course;
use App\Models\Internship;
use App\Models\SupervisorEvaluation;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Controlador para exibir o painel principal (dashboard) da área administrativa.
 *
 * Este controlador é "invokable" e sua única responsabilidade é coletar
 * diversas estatísticas do sistema e exibi-las na view do dashboard.
 */
class DashboardController extends Controller
{
    /**
     * Coleta dados e estatísticas e exibe o dashboard administrativo.
     *
     * @param  \Illuminate\Http\Request  $request  A requisição HTTP.
     * @return \Illuminate\View\View
     */
    public function __invoke(Request $request)
    {
        // Coleta estatísticas gerais sobre os estágios.
        $totalInternships = Internship::count();
        $deletedInternships = Internship::onlyTrashed()->count();
        $internshipsByStatus = [
            'pending' => Internship::where('status', InternshipStatus::PENDING->value)->count(),
            'awaiting_signature' => Internship::where('status', InternshipStatus::AWAITING_SIGNATURE->value)->count(),
            'in_progress' => Internship::where('status', InternshipStatus::IN_PROGRESS->value)->count(),
            'completed' => Internship::where('status', InternshipStatus::COMPLETED->value)->count(),
            'cancelled' => Internship::where('status', InternshipStatus::CANCELLED->value)->count(),
        ];

        // Coleta estatísticas sobre as empresas (partes concedentes).
        $totalCompanies = Company::count();
        $deletedCompanies = Company::onlyTrashed()->count();
        // Conta quantas empresas únicas possuem estágios em andamento.
        $activeCompanies = Internship::where('status', InternshipStatus::IN_PROGRESS->value)
            ->distinct('company_legal_identifier')
            ->count('company_legal_identifier');

        // Coleta estatísticas sobre os usuários do sistema.
        $totalUsers = User::count();
        $deletedUsers = User::onlyTrashed()->count();
        // Agrupa e conta os usuários por papel (role).
        $usersByRole = User::selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();

        // Coleta estatísticas sobre os cursos.
        $totalCourses = Course::count();
        $deletedCourses = Course::onlyTrashed()->count();

        // Coleta estatísticas sobre as avaliações de supervisor.
        $totalEvaluations = SupervisorEvaluation::count();
        $deletedEvaluations = SupervisorEvaluation::onlyTrashed()->count();

        // Busca os 10 estágios mais recentes com seus respectivos orientadores e cursos.
        $recentInternships = Internship::with(['advisor', 'course'])
            ->latest()
            ->take(10)
            ->get();

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
