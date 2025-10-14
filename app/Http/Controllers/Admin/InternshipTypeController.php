<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\InternshipTypeRequest;
use App\Models\InternshipType;
use App\Models\Course;
use App\Utils\SearchHelper;

class InternshipTypeController extends Controller
{
    /**
     * Exibe a lista de tipos de estágio.
     */
    public function index(Request $request)
    {
        $query = InternshipType::with('course');

        $showDeleted = $request->input('show_deleted') === '1';
        if ($showDeleted) {
            $query = $query->onlyTrashed();
        }

        // Filtro por nome
        if ($request->filled('search')) {
            SearchHelper::searchInField($query, $request->search, 'name');
        }

        // Filtro por curso
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $internshipTypes = $query->orderBy('name')->get();

        // Dados para os filtros
        $courses = Course::orderBy('name')->get();

        return view('admin.internship_types.index', compact('internshipTypes', 'courses', 'showDeleted'));
    }

    /**
     * Exibe o formulário para criar um novo tipo de estágio.
     */
    public function create()
    {
        $courses = Course::all();
        return view('admin.internship_types.create', compact('courses'));
    }

    /**
     * Armazena um novo tipo de estágio no banco de dados.
     */
    public function store(InternshipTypeRequest $request)
    {
        $data = $request->validated();
        InternshipType::create($data);
        return redirect()->route('admin.internship-types.index')
            ->with('message', 'Tipo de estágio cadastrado com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Exibe o formulário para editar um tipo de estágio específico.
     */
    public function edit(string $id)
    {
        $internshipType = InternshipType::findOrFail($id);
        $courses = Course::all();
        return view('admin.internship_types.edit', compact('internshipType', 'courses'));
    }

    /**
     * Atualiza um tipo de estágio específico no banco de dados.
     */
    public function update(InternshipTypeRequest $request, string $id)
    {
        $internshipType = InternshipType::findOrFail($id);
        $internshipType->update($request->validated());

        return redirect()->route('admin.internship-types.edit', $id)
            ->with('message', 'Tipo de estágio atualizado com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Remove o tipo de estágio especificado (soft delete).
     */
    public function destroy(string $id)
    {
        $internshipType = InternshipType::findOrFail($id);
        $internshipType->delete();

        return redirect()->route('admin.internship-types.index')
            ->with('message', 'Tipo de estágio excluído com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Restaura um tipo de estágio deletado (soft deleted).
     */
    public function restore($id)
    {
        $internshipType = InternshipType::onlyTrashed()->findOrFail($id);
        $internshipType->restore();

        return redirect()->route('admin.internship-types.index', ['show_deleted' => 1])
            ->with('message', 'Tipo de estágio restaurado com sucesso!')
            ->with('messageType', 'success');
    }
}
