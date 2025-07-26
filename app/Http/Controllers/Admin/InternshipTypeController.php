<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\InternshipTypeRequest;
use App\Models\InternshipType;
use App\Models\Course;

class InternshipTypeController extends Controller
{
    /**
     * Exibe a lista de tipos de estágio.
     */
    public function index()
    {
        $internshipTypes = InternshipType::with('course')->paginate(5);
        return view('admin.internship_types.index', compact('internshipTypes'));
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
            ->with('success', 'Tipo de estágio cadastrado com sucesso!');
    }

    /**
     * Exibe o formulário para editar um tipo de estágio específico.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Atualiza um tipo de estágio específico no banco de dados.
     */
    public function update(Request $request, string $id)
    {
        //
    }
}
