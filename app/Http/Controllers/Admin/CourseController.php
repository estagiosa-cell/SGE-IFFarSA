<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CourseLevel;
use App\Enums\CourseType;
use App\Http\Controllers\Controller;
use App\Http\Requests\CourseRequest;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Exibe uma listagem dos cursos.
     */
    public function index(Request $request)
    {
        $query = Course::with('coordinator');

        // Filtro por nome
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filtro por nível
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        // Filtro por tipo
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $courses = $query->orderBy('name')->paginate(5)->withQueryString();

        // Dados para os filtros
        $levels = CourseLevel::cases();
        $types = CourseType::cases();

        return view('admin.courses.index', compact('courses', 'levels', 'types'));
    }

    /**
     * Exibe o formulário para criação de um novo curso.
     */
    public function create()
    {
        $levels = CourseLevel::cases();
        $types = CourseType::cases();
        $coordinators = User::coordinators();

        return view('admin.courses.create', compact('levels', 'types', 'coordinators'));
    }

    /**
     * Armazena um novo curso no banco de dados.
     */
    public function store(CourseRequest $request)
    {
        Course::create($request->validated());

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Curso criado com sucesso!');
    }

    /**
     * Exibe o formulário para edição do curso especificado.
     */
    public function edit(string $id)
    {
        $course = Course::findOrFail($id);
        $levels = CourseLevel::cases();
        $types = CourseType::cases();
        $coordinators = User::coordinators();

        return view('admin.courses.edit', compact('course', 'levels', 'types', 'coordinators'));
    }

    /**
     * Atualiza o curso especificado no banco de dados.
     */
    public function update(CourseRequest $request, string $id)
    {
        $course = Course::findOrFail($id);
        $course->update($request->validated());

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Curso atualizado com sucesso!');
    }
}
