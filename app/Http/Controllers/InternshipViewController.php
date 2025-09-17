<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use Illuminate\Support\Facades\Auth;

class InternshipViewController extends Controller
{
    /**
     * Show the form for creating the resource.
     */
    public function index()
    {
        $user = Auth::user();

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
            $internships = $user->advisedInternships()
                ->with(['course', 'advisor'])
                ->orderByRaw($statusOrderSql)
                ->orderBy('updated_at', 'desc')
                ->paginate(15);
        } elseif ($user->can('is-coordenador')) {
            // Para coordenadores, buscar estágios dos cursos que coordena
            $courseIds = $user->coordinatedCourses()->pluck('id');

            $internships = Internship::whereIn('course_id', $courseIds)
                ->orWhere('advisor_id', $user->id)
                ->with(['course', 'advisor'])
                ->orderByRaw($statusOrderSql)
                ->orderBy('updated_at', 'desc')
                ->paginate(15);
        } else {
            abort(403, 'Acesso não autorizado.');
        }

        return view('internship-view.index', compact('internships'));
    }

    /**
     * Display the resource.
     */
    public function show()
    {
        //
    }
}
