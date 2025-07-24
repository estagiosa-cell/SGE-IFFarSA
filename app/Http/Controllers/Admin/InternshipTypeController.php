<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InternshipType;

class InternshipTypeController extends Controller
{
    /**
     * Exibe a lista de tipos de estágio.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        
    }

    /**
     * Exibe o formulário para criar um novo tipo de estágio.
     */
    public function create()
    {

    }

    /**
     * Armazena um novo tipo de estágio no banco de dados.
     */
    public function store(Request $request)
    {
        //
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
