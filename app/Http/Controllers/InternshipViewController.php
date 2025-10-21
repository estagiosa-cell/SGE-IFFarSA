<?php

namespace App\Http\Controllers;

use App\Enums\InternshipStatus;
use App\Models\Internship;
use App\Models\User;
use App\Utils\SearchHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InternshipViewController extends Controller
{
    /**
     * Show the form for creating the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $advisors = collect();
        $statusOptions = InternshipStatus::options();

        // Raw SQL para ordenação por prioridade de status
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
            $query = $user->advisedInternships();

            // Aplicar filtros
            $this->applyFilters($query, $request);

            $internships = $query->with(['course', 'advisor'])
                ->orderByRaw($statusOrderSql)
                ->orderBy('updated_at', 'desc')
                ->paginate(100);
        } elseif ($user->can('is-coordenador')) {
            // Para coordenadores, buscar estágios dos cursos que coordena
            $courseIds = $user->coordinatedCourses()->pluck('id');

            $query = Internship::where(function ($q) use ($courseIds, $user) {
                $q->whereIn('course_id', $courseIds)
                    ->orWhere('advisor_id', $user->id);
            });

            // Aplicar filtros
            $this->applyFilters($query, $request);

            $internships = $query->with(['course', 'advisor'])
                ->orderByRaw($statusOrderSql)
                ->latest('end_date')
                ->latest('updated_at')
                ->paginate(100);

            // Buscar orientadores que orientam estágios dos cursos coordenados
            $advisorIds = Internship::whereIn('course_id', $courseIds)
                ->distinct()
                ->pluck('advisor_id');

            $advisors = User::whereIn('id', $advisorIds)
                ->whereIn('role', ['orientador', 'coordenador'])
                ->whereNull('deactivated_at')
                ->orderBy('name')
                ->get();
        } else {
            abort(403, 'Acesso não autorizado.');
        }

        return view('internship-view.index', compact('internships', 'advisors', 'statusOptions'));
    }

    /**
     * Aplica os filtros na query de estágios
     */
    private function applyFilters($query, Request $request)
    {
        // Filtro por nome do estudante
        if ($request->filled('search')) {
            SearchHelper::searchInField($query, $request->search, 'student_name');
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por orientador (apenas para coordenadores)
        if ($request->filled('advisor') && Auth::user()->can('is-coordenador')) {
            $query->where('advisor_id', $request->advisor);
        }

        // Filtro por matrícula
        if ($request->filled('registration')) {
            $query->where('student_registration_number', 'like', '%'.$request->registration.'%');
        }

        return $query;
    }

    /**
     * Display the resource.
     */
    public function show(Internship $internship)
    {
        $user = Auth::user();

        // Verificar se o usuário tem permissão para visualizar este estágio
        $canView = false;

        if ($user->can('is-orientador')) {
            // Orientador pode ver estágios onde ele é o orientador
            $canView = $internship->advisor_id === $user->id;
        }

        if ($user->can('is-coordenador')) {
            // Coordenador pode ver estágios dos cursos que coordena
            $coordinatedCourseIds = $user->coordinatedCourses()->pluck('id');
            $canView = $canView || $coordinatedCourseIds->contains($internship->course_id);

            // Coordenador também pode ver estágios onde ele é orientador
            $canView = $canView || $internship->advisor_id === $user->id;
        }

        if (! $canView) {
            abort(403, 'Você não tem permissão para visualizar este estágio.');
        }

        // Carregar relacionamentos
        $internship->load(['advisor', 'course']);

        return view('internship-view.show', compact('internship'));
    }
}
