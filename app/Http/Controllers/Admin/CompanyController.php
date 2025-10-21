<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Utils\SearchHelper;
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

        $showDeleted = $request->input('show_deleted') === '1';
        if ($showDeleted) {
            $query = $query->onlyTrashed();
        }

        // Aplica o filtro de nome, se ele existir
        if ($searchName) {
            SearchHelper::searchInField($query, $searchName, 'name');
        }

        // Aplica o filtro de CPF/CNPJ, se ele existir
        if ($searchLegalIdentifier) {
            // Usa 'like' para permitir a busca mesmo que o usuário não digite a máscara
            $query->where('legal_identifier', 'like', '%'.$searchLegalIdentifier.'%');
        }

        // Executa a consulta, ordena os resultados pelo nome
        $companies = $query->orderBy('name')->paginate(100);

        // Retorna a view, passando a lista de empresas e os valores dos filtros
        return view('admin.companies.index', [
            'companies' => $companies,
            'searchName' => $searchName,
            'searchLegalIdentifier' => $searchLegalIdentifier,
            'showDeleted' => $showDeleted,
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

    /**
     * Import companies from CSV file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:20480', // máximo 20MB
        ]);

        try {
            $file = $request->file('csv_file');
            $path = $file->getRealPath();

            // Abre o arquivo CSV
            $csv = array_map('str_getcsv', file($path));

            // Remove o cabeçalho (primeira linha)
            $header = array_shift($csv);

            $imported = 0;
            $errors = [];

            foreach ($csv as $lineNumber => $row) {
                // Pula linhas vazias
                if (empty(array_filter($row))) {
                    continue;
                }

                // Mapeia os dados do CSV para os campos do modelo
                $companyData = $this->mapCsvToCompanyData($row);

                // Valida os dados obrigatórios
                $validation = $this->validateCompanyData($companyData, $lineNumber + 2);

                if (! empty($validation['errors'])) {
                    $errors = array_merge($errors, $validation['errors']);

                    continue;
                }

                // Cria a empresa
                Company::create($companyData);
                $imported++;
            }
            $message = "Importação concluída! {$imported} empresas importadas.";

            if (! empty($errors)) {
                $message .= ' '.count($errors).' erros encontrados.';

                return redirect()->back()
                    ->with('message', $message)
                    ->with('messageType', 'warning')
                    ->with('import_errors', $errors);
            }

            return redirect()->route('admin.companies.index')
                ->with('message', $message)
                ->with('messageType', 'success');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('message', 'Erro ao processar o arquivo: '.$e->getMessage())
                ->with('messageType', 'danger');
        }
    }

    /**
     * Map CSV row data to company model attributes.
     */
    private function mapCsvToCompanyData($row)
    {
        return [
            'legal_identifier' => $this->cleanLegalIdentifier($row[0] ?? ''),
            'name' => trim($row[1] ?? ''),
            'address_street' => trim($row[2] ?? ''),
            'address_number' => trim($row[3] ?? ''),
            'address_neighborhood' => trim($row[4] ?? ''),
            'address_city' => trim($row[5] ?? ''),
            'address_state' => trim($row[6] ?? ''),
            'address_zip' => $this->cleanZip($row[7] ?? ''),
            'representative_name' => trim($row[8] ?? ''),
            'representative_role' => trim($row[9] ?? ''),
            'phone' => $this->cleanPhone($row[10] ?? ''),
            'email' => trim($row[11] ?? ''),
            'field_of_activity' => trim($row[12] ?? ''),
            'professional_council' => trim($row[13] ?? ''),
            'council_registration_number' => trim($row[14] ?? ''),
            'process_number' => trim($row[15] ?? ''),
        ];
    }

    /**
     * Validate company data.
     */
    private function validateCompanyData($data, $lineNumber)
    {
        $errors = [];

        // Campos obrigatórios
        $requiredFields = [
            'legal_identifier' => 'CPF/CNPJ',
            'name' => 'Nome',
            'address_street' => 'Rua',
            'address_number' => 'Número',
            'address_neighborhood' => 'Bairro',
            'address_city' => 'Cidade',
            'address_state' => 'Estado',
            'address_zip' => 'CEP',
            'representative_name' => 'Nome do Representante',
            'representative_role' => 'Cargo do Representante',
            'field_of_activity' => 'Área de Atuação',
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty($data[$field])) {
                $errors[] = "Linha {$lineNumber}: Campo '{$label}' é obrigatório.";
            }
        }

        // Validação de CPF/CNPJ
        if (! empty($data['legal_identifier'])) {
            $cleaned = preg_replace('/[^0-9]/', '', $data['legal_identifier']);
            if (strlen($cleaned) !== 11 && strlen($cleaned) !== 14) {
                $errors[] = "Linha {$lineNumber}: CPF/CNPJ deve ter 11 ou 14 dígitos.";
            }
        }

        // Validação de email
        if (! empty($data['email']) && ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Linha {$lineNumber}: Email inválido.";
        }

        return ['errors' => $errors];
    }

    /**
     * Clean and format legal identifier (CPF/CNPJ).
     */
    private function cleanLegalIdentifier($value)
    {
        return preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Clean and format ZIP code.
     */
    private function cleanZip($value)
    {
        return preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Clean and format phone number.
     */
    private function cleanPhone($value)
    {
        return preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Restaura uma empresa deletada (soft delete).
     */
    public function restore($id)
    {
        $company = Company::onlyTrashed()->findOrFail($id);
        $company->restore();

        return redirect()->route('admin.companies.index', ['show_deleted' => 1])
            ->with('message', 'Parte Concedente restaurada com sucesso!')
            ->with('messageType', 'success');
    }
}
