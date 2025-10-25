<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Internship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse; // Importe esta classe

class ReportController extends Controller
{
    /**
     * Exibe a página de filtros para o relatório.
     */
    public function __invoke(Request $request)
    {
        $courses = collect();
        $user = Auth::user();

        if ($user->can('is-admin')) {
            // Admin vê todos os cursos
            $courses = Course::orderBy('name')->get();
        } elseif ($user->can('is-coordenador')) {
            // Coordenador vê apenas os cursos que ele coordena
            $courses = Course::where('coordinator_id', $user->id)->orderBy('name')->get();
        }

        return view('reports.internships', compact('courses'));
    }

    /**
     * Gera e exporta os dados dos estágios em um arquivo CSV.
     */
    public function exportInternships(Request $request)
    {
        $user = Auth::user();
        $rules = [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];

        if (! $user->can('is-orientador')) {
            $rules['course_id'] = 'required|string';
        }

        $request->validate(
            $rules,
            [
                'course_id.required' => 'O filtro principal é obrigatório.',
                'start_date.required' => 'A data de início é obrigatória.',
                'end_date.required' => 'A data de fim é obrigatória.',
                'end_date.after_or_equal' => 'A data de fim deve ser posterior ou igual à data de início.',
            ]
        );

        $query = Internship::with(['course', 'advisor']);

        if ($user->can('is-admin')) {
            if ($request->course_id != 'all_courses') {
                $query->where('course_id', $request->course_id);
            }
        } elseif ($user->can('is-coordenador')) {
            if ($request->course_id == 'my_advisees') {
                $query->where('advisor_id', $user->id);
            } elseif ($request->course_id != 'all_courses') {
                $query->where('course_id', $request->course_id);
            } else {
                $coordinatorCourses = Course::where('coordinator_id', $user->id)->pluck('id');
                $query->whereIn('course_id', $coordinatorCourses);
            }
        } elseif ($user->can('is-orientador')) {
            $query->where('advisor_id', $user->id);
        }

        $query->where('start_date', '>=', $request->start_date)
            ->where('end_date', '<=', $request->end_date);

        $internships = $query->get();

        // Adiciona mais detalhes ao nome do arquivo com base nos filtros do usuário
        if ($request->course_id === 'all_courses') {
            $courseName = $user->can('is-coordenador') ? 'todos_meus_cursos' : 'todos_cursos';
        } elseif ($request->course_id === 'my_advisees' || $user->can('is-orientador')) {
            $userName = str_replace(' ', '_', iconv('UTF-8', 'ASCII//TRANSLIT', $user->name));
            $courseName = "orientandos_{$userName}";
        } else {
            $courseName = Course::find($request->course_id)->name ?? 'curso_desconhecido';
            $courseName = str_replace(' ', '_', iconv('UTF-8', 'ASCII//TRANSLIT', $courseName));
        }

        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $fileName = "relatorio_estagios_{$courseName}_{$startDate}_{$endDate}.csv";

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=$fileName",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        // Define o cabeçalho do CSV
        $columns = [
            'Curso', 'Matrícula', 'Nome do Aluno', 'Concedente', 'CNPJ da Concedente',
            'Data de Início', 'Data de Fim', 'Orientador', 'Supervisor', 'Status', 'Nota da Avaliação',
        ];

        $callback = function () use ($internships, $columns) {
            // Cria um "arquivo" em memória para escrita
            $file = fopen('php://output', 'w');

            // Adiciona o BOM (Byte Order Mark) para garantir a correta interpretação de caracteres UTF-8 no Excel
            fwrite($file, "\xEF\xBB\xBF");

            fputcsv($file, $columns);

            foreach ($internships as $internship) {
                // Monta a linha do CSV
                $row = [
                    $internship->course->name ?? 'Não informado',
                    $internship->student_registration_number ?? 'Não informado',
                    $internship->student_name ?? 'Não informado',
                    $internship->company_name ?? 'Não informado',
                    $internship->company_legal_identifier ?? 'Não informado',
                    $internship->start_date->format('d/m/Y'),
                    $internship->end_date->format('d/m/Y'),
                    $internship->advisor->name ?? 'Não informado',
                    $internship->supervisor_name ?? 'Não informado',
                    $internship->status->value ?? 'Não informado',
                    $internship->evaluation_grade ?? 'Não avaliado',
                ];

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
