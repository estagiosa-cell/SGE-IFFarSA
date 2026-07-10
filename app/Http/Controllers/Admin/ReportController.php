<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InternshipStatus;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\InternshipReportService;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ReportController extends Controller
{
    /**
     * Exibe o dashboard visual de relatórios gerenciais.
     */
    public function index(Request $request, InternshipReportService $reportService)
    {
        // Captura os filtros
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Dados para filtros
        $courses = Course::orderBy('name')->get();
        $statusOptions = InternshipStatus::options();

        // Aba 1: Fluxo Documental
        $processingTimes = $reportService->getProcessingTimes($startDate, $endDate);
        $processingMetrics = $reportService->getProcessingTimeMetrics($startDate, $endDate);
        $monthlyProcessing = $reportService->getMonthlyProcessingTimes($startDate, $endDate);
        $onTimeForwarding = $reportService->getOnTimeForwarding($startDate, $endDate);
        $onTimeByCourse = $reportService->getOnTimeForwardingByCourse($startDate, $endDate);
        $internshipStatuses = $reportService->getInternshipStatuses($startDate, $endDate);
        $cancellations = $reportService->getCancellationsByReason($startDate, $endDate);
        $pendingDocuments = $reportService->getPendingDocuments($startDate, $endDate);

        // Aba 2: Acompanhamento da Prática
        $completionOnTime = $reportService->getCompletionOnTime($startDate, $endDate);
        $amendmentsCount = $reportService->getAmendmentsCount($startDate, $endDate);
        $amendmentsByReason = $reportService->getAmendmentsByReason($startDate, $endDate);

        // Aba 3: Desempenho
        $globalGradeMetrics = $reportService->getGlobalGradeMetrics($startDate, $endDate);
        $gradeDistribution = $reportService->getGradeDistribution($startDate, $endDate);

        // Aba 4: Relatórios por Curso
        $internshipsByCourse = $reportService->getInternshipsByCourse($startDate, $endDate);
        $supervisorsMetrics = $reportService->getSupervisorsMetrics($startDate, $endDate);
        $advisorsMetrics = $reportService->getAdvisorsMetrics($startDate, $endDate);
        $averageGrades = $reportService->getAverageGradesByCourse($startDate, $endDate);
        $topCompanies = $reportService->getTopCompanies($startDate, $endDate);
        $companiesByCourse = $reportService->getGrantingCompaniesByCourse($startDate, $endDate);

        // Histórico Global (com paginação 50, preservando os parâmetros da URL)
        $globalActivities = Activity::with('causer', 'subject')
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return view('admin.reports.index', compact(
            'startDate',
            'endDate',
            'courses',
            'statusOptions',
            'processingTimes',
            'processingMetrics',
            'monthlyProcessing',
            'onTimeForwarding',
            'onTimeByCourse',
            'internshipStatuses',
            'cancellations',
            'pendingDocuments',
            'completionOnTime',
            'amendmentsCount',
            'amendmentsByReason',
            'globalGradeMetrics',
            'gradeDistribution',
            'internshipsByCourse',
            'supervisorsMetrics',
            'advisorsMetrics',
            'averageGrades',
            'topCompanies',
            'companiesByCourse',
            'globalActivities'
        ));
    }
}
