<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseRequest;
use App\Models\Course;
use App\Models\User;
use App\Utils\SearchHelper;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Exibe uma listagem dos cursos.
     */
    public function index(Request $request)
    {
        $query = Course::with('coordinator');

        $showDeleted = $request->input('show_deleted') === '1';
        if ($showDeleted) {
            $query = $query->onlyTrashed();
        }

        // Filtro por nome
        if ($request->filled('search')) {
            SearchHelper::searchInField($query, $request->search, 'name');
        }

        $courses = $query->orderBy('name')->get();

        return view('admin.courses.index', compact('courses', 'showDeleted'));
    }

    /**
     * Exibe o formulário para criação de um novo curso.
     */
    public function create()
    {
        $coordinators = User::coordinators();

        return view('admin.courses.create', compact('coordinators'));
    }

    /**
     * Armazena um novo curso no banco de dados.
     */
    public function store(CourseRequest $request)
    {
        Course::create($request->validated());

        return redirect()
            ->route('admin.courses.index')
            ->with('message', 'Curso criado com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Exibe o formulário para edição do curso especificado.
     */
    public function edit(string $id)
    {
        $course = Course::findOrFail($id);
        $coordinators = User::coordinators();

        return view('admin.courses.edit', compact('course', 'coordinators'));
    }

    /**
     * Atualiza o curso especificado no banco de dados.
     */
    public function update(CourseRequest $request, string $id)
    {
        $course = Course::findOrFail($id);
        $course->update($request->validated());

        return redirect()
            ->route('admin.courses.edit', $id)
            ->with('message', 'Curso atualizado com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Remove o curso especificado (soft delete).
     */
    public function destroy(string $id)
    {
        $course = Course::findOrFail($id);
        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('message', 'Curso excluído com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Restaura um curso deletado (soft deleted).
     */
    public function restore($id)
    {
        $course = Course::onlyTrashed()->findOrFail($id);
        $course->restore();

        return redirect()->route('admin.courses.index', ['show_deleted' => 1])
            ->with('message', 'Curso restaurado com sucesso!')
            ->with('messageType', 'success');
    }
}
