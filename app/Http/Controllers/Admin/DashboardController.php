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

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // Estatísticas de estágios
        $totalInternships = Internship::count();
        $deletedInternships = Internship::onlyTrashed()->count();
        $internshipsByStatus = [
            'pending' => Internship::where('status', InternshipStatus::PENDING->value)->count(),
            'awaiting_signature' => Internship::where('status', InternshipStatus::AWAITING_SIGNATURE->value)->count(),
            'in_progress' => Internship::where('status', InternshipStatus::IN_PROGRESS->value)->count(),
            'completed' => Internship::where('status', InternshipStatus::COMPLETED->value)->count(),
            'cancelled' => Internship::where('status', InternshipStatus::CANCELLED->value)->count(),
        ];

        // Estatísticas de empresas (partes concedentes)
        $totalCompanies = Company::count();
        $deletedCompanies = Company::onlyTrashed()->count();
        // Contar empresas únicas que têm estágios em andamento (via company_legal_identifier)
        $activeCompanies = Internship::where('status', InternshipStatus::IN_PROGRESS->value)
            ->distinct('company_legal_identifier')
            ->count('company_legal_identifier');

        // Estatísticas de usuários
        $totalUsers = User::count();
        $deletedUsers = User::onlyTrashed()->count();
        $usersByRole = User::selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();

        // Estatísticas de cursos
        $totalCourses = Course::count();
        $deletedCourses = Course::onlyTrashed()->count();

        // Estatísticas de avaliações do supervisor
        $totalEvaluations = SupervisorEvaluation::count();
        $deletedEvaluations = SupervisorEvaluation::onlyTrashed()->count();

        // Estágios recentes
        $recentInternships = Internship::with(['advisor', 'course'])
            ->latest()
            ->take(10)
            ->get();

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
