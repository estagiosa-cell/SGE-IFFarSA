<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Pega os valores dos filtros da requisição
        $searchName = $request->get('name');
        $searchLegalIdentifier = $request->get('legal_identifier');

        // Inicia a construção da consulta ao banco de dados
        $query = Company::query();

        // Aplica o filtro de nome, se ele existir
        if ($searchName) {
            // Usa 'like' com '%' para buscar por partes do nome
            $query->where('name', 'like', '%' . $searchName . '%');
        }

        // Aplica o filtro de CPF/CNPJ, se ele existir
        if ($searchLegalIdentifier) {
            // Usa 'like' para permitir a busca mesmo que o usuário não digite a máscara
            $query->where('legal_identifier', 'like', '%' . $searchLegalIdentifier . '%');
        }

        // Executa a consulta, ordena os resultados pelo nome e pagina os resultados
        $companies = $query->orderBy('name')->paginate(15)->withQueryString();

        // Retorna a view, passando a lista de empresas e os valores dos filtros
        return view('admin.companies.index', [
            'companies' => $companies,
            'searchName' => $searchName,
            'searchLegalIdentifier' => $searchLegalIdentifier,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.companies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompanyRequest $request)
    {
        Company::create($request->validated());

        // Redireciona o usuário para a página de listagem (index)
        // com uma mensagem de sucesso na sessão.
        return redirect()->route('admin.companies.index')
            ->with('message', 'Parte Concedente cadastrada com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $validatedData = $request->validated();

        $company->update($validatedData);

        return redirect()->back()
            ->with('message', 'Parte Concedente alterada com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        $company->delete();

        return redirect()->route('admin.companies.index')
            ->with('message', 'Parte Concedente excluída com sucesso!')
            ->with('messageType', 'success');
    }
}
