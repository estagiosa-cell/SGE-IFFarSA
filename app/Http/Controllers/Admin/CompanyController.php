<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

/**
 * Controlador para gerenciar as Partes Concedentes (empresas) no painel administrativo.
 *
 * Este controlador lida com a listagem, criação, edição, exclusão,
 * importação e restauração de empresas que oferecem estágios.
 */
class CompanyController extends Controller
{
    use AuthorizesRequests;

    /**
     * Exibe uma lista de partes concedentes com filtros.
     *
     * @param  Request  $request  A requisição HTTP com os parâmetros de filtro.
     * @return View
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Company::class);

        // Pega os valores dos filtros da requisição.
        $searchName = $request->get('name');
        $searchLegalIdentifier = $request->get('legal_identifier');
        $searchCity = $request->get('address_city');

        // Inicia a construção da consulta ao banco de dados.
        $query = Company::query();

        // Verifica se o filtro 'show_deleted' está ativo para incluir empresas removidas (soft delete).
        $showDeleted = $request->input('show_deleted') === '1';
        if ($showDeleted) {
            $query = $query->onlyTrashed();
        }

        // Aplica o filtro de busca por CPF/CNPJ, se ele existir.
        if ($searchLegalIdentifier) {
            // Remove a máscara da string de busca e converte para maiúsculo (para o CNPJ alfanumérico)
            $cleanSearchIdentifier = strtoupper(preg_replace('/[.\-\/\s]/', '', $searchLegalIdentifier));
            // Usa 'like' para permitir a busca parcial.
            $query->where('legal_identifier', 'like', '%'.$cleanSearchIdentifier.'%');
        }

        $orderedQuery = $query->orderBy('name');

        // Combine name and city into a single search term so SearchHelper
        // can search both `name` and `address_city` at once. Empty term falls
        // back to normal pagination inside SearchHelper.
        $combinedSearch = trim((string) $searchName.' '.(string) $searchCity);

        $companies = $orderedQuery->search($combinedSearch === '' ? null : $combinedSearch, ['name', 'address_city'])
            ->paginate(100)
            ->withQueryString();

        $activeFiltersCount = collect([
            $searchName,
            $searchLegalIdentifier,
            $searchCity,
        ])->filter(fn ($v) => filled($v))->count();

        // Busca todas as cidades cadastradas (distintas) para o modal de exportação
        $availableCities = Company::select('address_city')
            ->distinct()
            ->whereNotNull('address_city')
            ->where('address_city', '!=', '')
            ->orderBy('address_city')
            ->pluck('address_city');

        // Retorna a view, passando a lista de empresas e os valores dos filtros para preenchimento.
        return view('admin.companies.index', [
            'companies' => $companies,
            'searchName' => $searchName,
            'searchLegalIdentifier' => $searchLegalIdentifier,
            'searchCity' => $searchCity,
            'showDeleted' => $showDeleted,
            'activeFiltersCount' => $activeFiltersCount,
            'availableCities' => $availableCities,
        ]);
    }

    /**
     * Exibe o formulário para criar uma nova parte concedente.
     *
     * @return View
     */
    public function create()
    {
        $this->authorize('create', Company::class);

        return view('admin.companies.create');
    }

    /**
     * Armazena uma nova parte concedente no banco de dados.
     *
     * @param  StoreCompanyRequest  $request  A requisição validada.
     * @return RedirectResponse
     */
    public function store(StoreCompanyRequest $request)
    {
        // Cria a empresa com os dados validados pela StoreCompanyRequest.
        Company::create($request->validated());

        // Redireciona o usuário para a página de listagem (index)
        // com uma mensagem de sucesso na sessão.
        return redirect()->route('admin.companies.index')
            ->with('message', 'Parte Concedente cadastrada com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Exibe o formulário para editar uma parte concedente específica.
     *
     * @param  Company  $company  A instância da empresa injetada pelo Route Model Binding.
     * @return View
     */
    public function edit(Company $company)
    {
        $this->authorize('update', $company);

        return view('admin.companies.edit', compact('company'));
    }

    /**
     * Atualiza uma parte concedente específica no banco de dados.
     *
     * @param  UpdateCompanyRequest  $request  A requisição validada.
     * @param  Company  $company  A instância da empresa injetada pelo Route Model Binding.
     * @return RedirectResponse
     */
    public function update(UpdateCompanyRequest $request, Company $company)
    {
        // Obtém os dados validados da UpdateCompanyRequest.
        $validatedData = $request->validated();

        // Atualiza os dados da empresa.
        $company->update($validatedData);

        // Redireciona de volta para a página de edição com uma mensagem de sucesso.
        return redirect()->back()
            ->with('message', 'Parte Concedente alterada com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Remove uma parte concedente do sistema (soft delete).
     *
     * @param  Company  $company  A instância da empresa injetada pelo Route Model Binding.
     * @return RedirectResponse
     */
    public function destroy(Company $company)
    {
        try {
            $this->authorize('delete', $company);
            $company->delete();
        } catch (AuthorizationException $e) {
            return redirect()->back()
                ->with('message', $e->getMessage())
                ->with('messageType', 'danger');
        }

        return redirect()->route('admin.companies.index')
            ->with('message', 'Parte Concedente excluída com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Importa partes concedentes a partir de um arquivo CSV.
     *
     * @param  Request  $request  A requisição contendo o arquivo CSV.
     * @return RedirectResponse
     */
    public function import(Request $request)
    {
        $this->authorize('create', Company::class);

        // Valida se o arquivo foi enviado e se é um CSV válido.
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:20480', // máximo 20MB
        ]);

        try {
            $file = $request->file('csv_file');
            $path = $file->getRealPath();

            $imported = 0;
            $errors = [];
            $batch = [];

            // Lê o arquivo CSV linha a linha em vez de carregar tudo na memória
            $handle = fopen($path, 'r');

            // Pula a primeira linha (cabeçalho)
            if ($handle !== false) {
                fgetcsv($handle);
            }

            $lineNumber = 1; // Começa a contagem após o cabeçalho (que foi lido acima)

            while (($row = fgetcsv($handle)) !== false) {
                $lineNumber++;

                // Pula linhas que estejam completamente vazias no CSV.
                if (empty(array_filter($row))) {
                    continue;
                }

                // Mapeia os dados da linha do CSV para os atributos do modelo Company.
                $companyData = $this->mapCsvToCompanyData($row);

                // Valida os dados mapeados para garantir a integridade.
                $validation = $this->validateCompanyData($companyData, $lineNumber);

                if (! empty($validation['errors'])) {
                    $errors = array_merge($errors, $validation['errors']);

                    continue; // Pula para a próxima linha se houver erros.
                }

                // Adiciona a linha válida ao batch, com timestamps.
                $batch[] = $companyData + [
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Insere em banco a cada 100 registros para não estourar memória.
                if (count($batch) >= 100) {
                    Company::insert($batch);
                    $imported += count($batch);
                    $batch = [];
                }
            }

            // Insere qualquer registro remanescente que não alcançou 100.
            if (! empty($batch)) {
                Company::insert($batch);
                $imported += count($batch);
            }

            if ($handle !== false) {
                fclose($handle);
            }

            $message = "Importação concluída! {$imported} empresas importadas.";

            // Se houver erros de validação, retorna com uma mensagem de aviso e a lista de erros.
            if (! empty($errors)) {
                $message .= ' '.count($errors).' erros encontrados.';

                return redirect()->back()
                    ->with('message', $message)
                    ->with('messageType', 'warning')
                    ->with('import_errors', $errors);
            }

            // Se tudo ocorrer bem, retorna com uma mensagem de sucesso.
            return redirect()->route('admin.companies.index')
                ->with('message', $message)
                ->with('messageType', 'success');
        } catch (\Exception $e) {
            // Em caso de exceção (ex: falha na leitura do arquivo), retorna um erro genérico.
            return redirect()->back()
                ->with('message', 'Erro ao processar o arquivo: '.$e->getMessage())
                ->with('messageType', 'danger');
        }
    }

    /**
     * Mapeia os dados de uma linha do CSV para os atributos do modelo Company.
     *
     * @param  array  $row  A linha de dados do arquivo CSV.
     * @return array Os dados mapeados e limpos.
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
     * Valida os dados de uma empresa antes da importação.
     *
     * @param  array  $data  Os dados da empresa a serem validados.
     * @param  int  $lineNumber  O número da linha no arquivo CSV para referência de erro.
     * @return array Um array contendo os erros de validação.
     */
    private function validateCompanyData($data, $lineNumber)
    {
        $errors = [];

        // Define os campos que são obrigatórios para a criação da empresa.
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

        // Valida o formato do CPF/CNPJ (deve ter 11 ou 14 caracteres, podendo conter letras para CNPJ alfanumérico).
        if (! empty($data['legal_identifier'])) {
            // Remove apenas a máscara (pontos, barra, traço) para contar os caracteres.
            $cleaned = preg_replace('/[.\-\/\s]/', '', $data['legal_identifier']);
            if (strlen($cleaned) !== 11 && strlen($cleaned) !== 14) {
                $errors[] = "Linha {$lineNumber}: CPF/CNPJ deve ter 11 ou 14 caracteres.";
            }
        }

        // Valida o formato do e-mail.
        if (! empty($data['email']) && ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Linha {$lineNumber}: Email inválido.";
        }

        return ['errors' => $errors];
    }

    /**
     * Limpa e normaliza o CPF/CNPJ, removendo apenas os separadores da máscara.
     *
     * Preserva letras maiúsculas para suportar o novo formato alfanumérico de CNPJ
     * (Resolução DREI nº 81/2024).
     *
     * @param  string  $value  O valor do CPF/CNPJ.
     * @return string O valor limpo (sem máscara, em maiúsculas).
     */
    private function cleanLegalIdentifier($value)
    {
        // Remove apenas a máscara (pontos, barra, traço, espaços) e converte para maiúsculas.
        return strtoupper(preg_replace('/[.\-\/\s]/', '', trim($value)));
    }

    /**
     * Limpa e formata o CEP, removendo caracteres não numéricos.
     *
     * @param  string  $value  O valor do CEP.
     * @return string O valor limpo.
     */
    private function cleanZip($value)
    {
        return preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Limpa e formata o número de telefone, removendo caracteres não numéricos.
     *
     * @param  string  $value  O valor do telefone.
     * @return string O valor limpo.
     */
    private function cleanPhone($value)
    {
        return preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Restaura uma parte concedente que foi removida via soft delete.
     *
     * @param  string  $id  O ID da empresa a ser restaurada.
     * @return RedirectResponse
     */
    public function restore($id)
    {
        // Busca a empresa apenas na lixeira (onlyTrashed).
        $company = Company::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $company);
        $company->restore();

        // Redireciona de volta para a lista de empresas excluídas.
        return redirect()->route('admin.companies.index', ['show_deleted' => 1])
            ->with('message', 'Parte Concedente restaurada com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Exporta as empresas filtradas para CSV ou ZIP compatível com o novo SGE.
     */
    public function exportCsv(Request $request): StreamedResponse|RedirectResponse
    {
        $this->authorize('viewAny', Company::class);
        $validated = $request->validate([
            'format' => ['nullable', 'in:contacts,sge'],
            'address_city' => ['nullable', 'string', 'max:255'],
        ]);
        $isSge = ($validated['format'] ?? 'contacts') === 'sge';
        $searchCity = $validated['address_city'] ?? null;
        $query = Company::query();
        if ($searchCity) {
            if ($isSge) {
                $query->where('address_city', $searchCity);
            } else {
                $query->search($searchCity, ['address_city']);
            }
        }
        $query->orderBy('address_city')->orderBy('name')->orderBy('id');
        if (! $query->exists()) {
            return redirect()->back()->with('message', 'Nenhuma empresa encontrada com os filtros informados.')
                ->with('messageType', 'warning');
        }

        if ($isSge) {
            return response()->streamDownload(function () use ($query): void {
                $path = tempnam(sys_get_temp_dir(), 'sge-export-');
                if ($path === false) {
                    throw new RuntimeException('Não foi possível preparar a exportação.');
                }
                $zip = new ZipArchive;
                $opened = false;
                try {
                    if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
                        throw new RuntimeException('Não foi possível criar o ZIP da exportação.');
                    }
                    $opened = true;
                    $part = 0;
                    $query->chunk(500, function (Collection $companies) use ($zip, &$part): void {
                        $records = $companies->map(fn (Company $company): array => $this->sgeCsvRecord($company))->all();
                        $columns = [
                            'tipo_documento', 'documento', 'nome', 'representante_nome', 'representante_cargo', 'atividade',
                            'telefone', 'email', 'conselho_profissional', 'registro_conselho', 'processo_credenciamento',
                            'logradouro', 'numero', 'bairro', 'cep', 'cidade', 'uf',
                        ];
                        $stream = fopen('php://temp', 'w+');
                        if ($stream === false) {
                            throw new RuntimeException('Não foi possível preparar o CSV.');
                        }
                        try {
                            $this->writeExportCsv($stream, $columns, $records, true);
                            rewind($stream);
                            $contents = stream_get_contents($stream);
                            if ($contents === false || ! $zip->addFromString('concedentes_sge_parte_'.(++$part).'.csv', $contents)) {
                                throw new RuntimeException('Não foi possível adicionar o CSV ao ZIP.');
                            }
                        } finally {
                            fclose($stream);
                        }
                    });
                    $closed = $zip->close();
                    $opened = false;
                    if (! $closed) {
                        throw new RuntimeException('Não foi possível concluir o ZIP da exportação.');
                    }
                    readfile($path);
                } finally {
                    if ($opened) {
                        $zip->close();
                    }
                    unlink($path);
                }
            }, 'concedentes_sge.zip', ['Content-Type' => 'application/zip', 'Cache-Control' => 'private, no-store']);
        }

        $records = $query->get()->map(fn (Company $company): array => [
            (string) $company->name, (string) $company->address_city, (string) $company->phone, (string) $company->email,
        ])->all();

        return response()->streamDownload(function () use ($records): void {
            $stream = fopen('php://output', 'w');
            if ($stream === false) {
                throw new RuntimeException('Não foi possível preparar o CSV.');
            }
            try {
                $this->writeExportCsv($stream, ['Nome/Razão Social', 'Cidade', 'Telefone', 'Email'], $records);
            } finally {
                fclose($stream);
            }
        }, 'empresas_'.date('Y-m-d_H-i-s').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
    }

    /**
     * @param  resource  $stream
     * @param  list<string>  $columns
     * @param  list<list<string>>  $records
     */
    private function writeExportCsv($stream, array $columns, array $records, bool $isSge = false): void
    {
        $escapeMetadata = $isSge && collect($records)->contains(fn (array $record): bool => collect($record)->contains(fn (string $value): bool => $this->needsCsvEscape($value)));
        if ($escapeMetadata) {
            $columns = [...$columns, '__sge_formato', '__sge_linha_original', '__sge_erros', '__sge_excedentes', '__sge_escape'];
        }
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, $columns, ';', '"', '', "\r\n");
        $line = 2;
        foreach ($records as $record) {
            $endLine = $line + array_sum(array_map(static fn (string $value): int => substr_count($value, "\n"), $record));
            if ($escapeMetadata) {
                $escaped = [];
                foreach ($record as $column => &$value) {
                    if ($this->needsCsvEscape($value)) {
                        $value = "'".$value;
                        $escaped[] = $columns[$column];
                    }
                }
                unset($value);
                $record = [...$record, 'erros-v1', ($line === $endLine ? (string) $line : $line.'–'.$endLine), '', '[]', json_encode($escaped, JSON_THROW_ON_ERROR)];
            }
            fputcsv($stream, $record, ';', '"', '', "\r\n");
            $line = $endLine + 1;
        }
    }

    /** @return list<string> */
    private function sgeCsvRecord(Company $company): array
    {
        $document = $this->cleanLegalIdentifier((string) $company->legal_identifier);

        return [
            strlen($document) === 11 && ctype_digit($document) ? 'cpf' : 'cnpj',
            $document, (string) $company->name, (string) $company->representative_name,
            (string) $company->representative_role, (string) $company->field_of_activity,
            (string) $company->phone, (string) $company->email, (string) $company->professional_council,
            (string) $company->council_registration_number, (string) $company->process_number,
            (string) $company->address_street, (string) $company->address_number,
            (string) $company->address_neighborhood, (string) $company->address_zip,
            (string) $company->address_city, (string) $company->address_state,
        ];
    }

    private function needsCsvEscape(string $value): bool
    {
        return preg_match('/^(?:[ \t\r\n]*[=+@-]|[\t\r\n])/u', $value) === 1;
    }
}
