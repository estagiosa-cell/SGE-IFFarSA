<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccreditationRequest;
use App\Models\Accreditation;
use Illuminate\Http\Request;

class AccreditationController extends Controller
{
    /**
     * Exibe uma listagem dos credenciamentos.
     */
    public function index(Request $request)
    {
        $query = Accreditation::query();

        // Filtros
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('cpf', 'like', '%' . $request->search . '%')
                    ->orWhere('process_number', 'like', '%' . $request->search . '%');
            });
        }

        $accreditations = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.accreditations.index', compact('accreditations'));
    }

    /**
     * Exibe o formulário para criação de um novo credenciamento.
     */
    public function create()
    {
        return view('admin.accreditations.create');
    }

    /**
     * Armazena um novo credenciamento no banco de dados.
     */
    public function store(AccreditationRequest $request)
    {
        Accreditation::create($request->validated());

        return redirect()
            ->route('admin.accreditations.index')
            ->with('message', 'Credenciamento criado com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Exibe o formulário para edição do credenciamento especificado.
     */
    public function edit(string $id)
    {
        $accreditation = Accreditation::findOrFail($id);

        return view('admin.accreditations.edit', compact('accreditation'));
    }

    /**
     * Atualiza o credenciamento especificado no banco de dados.
     */
    public function update(AccreditationRequest $request, string $id)
    {
        $accreditation = Accreditation::findOrFail($id);
        $accreditation->update($request->validated());

        return redirect()
            ->route('admin.accreditations.edit', $id)
            ->with('message', 'Credenciamento atualizado com sucesso!')
            ->with('messageType', 'success');
    }
}
