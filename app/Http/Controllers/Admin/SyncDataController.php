<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InternshipStatus;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Course;
use App\Models\Internship;
use App\Models\SupervisorEvaluation;
use App\Models\User;
use App\Services\FuzzySearchService;
use App\Services\GoogleApiService;
use App\Utils\InternshipEndDate;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * Controlador para sincronização de dados de planilhas do Google.
 *
 * Este controlador é "invokable" e sua responsabilidade é orquestrar a importação
 * de dados de duas planilhas distintas: uma para novos registros de estágio e
 * outra para avaliações de supervisores. Ele utiliza serviços para interagir
 * com a API do Google e para realizar buscas por similaridade (fuzzy search).
 */
class SyncDataController extends Controller
{
    use AuthorizesRequests;

    /**
     * Orquestra a sincronização de dados de estágios e avaliações de supervisores.
     *
     * Este método invoca as funções de sincronização para estágios e avaliações,
     * coleta as mensagens de resultado de cada processo e redireciona o usuário
     * de volta ao dashboard com um resumo das operações.
     *
     * @param  \Illuminate\Http\Request  $request  A requisição HTTP.
     * @param  \App\Services\GoogleApiService  $googleService  Serviço para interagir com as APIs do Google.
     * @param  \App\Services\FuzzySearchService  $fuzzySearch  Serviço para busca por similaridade.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, GoogleApiService $googleService, FuzzySearchService $fuzzySearch)
    {
        $this->authorize('viewAny', Internship::class);

        $syncResult = [
            'internships' => ['processed' => 0, 'errors' => [], 'warnings' => []],
            'evaluations' => ['processed' => 0, 'errors' => [], 'warnings' => []],
            'form' => 'disabled',
        ];

        // Tenta sincronizar os dados de estágios.
        try {
            $syncResult['internships'] = $this->syncInternshipsData($googleService, $fuzzySearch);
        } catch (\Exception $e) {
            $syncResult['internships']['errors'][] = ['line' => null, 'student' => 'Sistema', 'reason' => $e->getMessage()];
        }

        // Tenta sincronizar as avaliações de supervisores.
        try {
            $syncResult['evaluations'] = $this->syncSupervisorEvaluations($googleService);
        } catch (\Exception $e) {
            $syncResult['evaluations']['errors'][] = ['line' => null, 'student' => 'Sistema', 'reason' => $e->getMessage()];
        }

        // Tenta sincronizar a lista de orientadores no formulário.
        try {
            $syncResult['form'] = $this->syncAdvisorsToForm($googleService);
        } catch (\Exception $e) {
            // Apenas registra falso se falhar a sincronização do form
            $syncResult['form'] = 'error';
        }

        return redirect()->route('admin.dashboard')->with('sync_result', $syncResult);
    }

    /**
     * Sincroniza os dados de estágios a partir de uma planilha do Google.
     *
     * Este método lê uma planilha, mapeia cada coluna para um campo do modelo `Internship`,
     * realiza validações, busca por registros relacionados (curso, orientador, empresa)
     * e, se tudo estiver correto, cria um novo registro de estágio no banco de dados.
     * Ao final, marca a linha como processada na planilha.
     *
     * @param  \App\Services\GoogleApiService  $googleService  Serviço para interagir com a API do Google.
     * @param  \App\Services\FuzzySearchService  $fuzzySearch  Serviço para busca por similaridade.
     * @return int O número de estágios processados com sucesso.
     *
     * @throws \Exception Se ocorrer um erro crítico durante o processo.
     */
    private function syncInternshipsData(GoogleApiService $googleService, FuzzySearchService $fuzzySearch): array
    {
        $spreadsheetId = config('services.google.sheets.data_collection_id');

        if (! $spreadsheetId) {
            throw new \Exception('ID da planilha de estágios não configurado');
        }

        $client = $googleService->getClient();
        $service = new \Google_Service_Sheets($client);

        $range = "'Respostas ao formulário 1'!B2:BJ";

        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $rows = $response->getValues();

        if (empty($rows)) {
            return ['processed' => 0, 'errors' => [], 'warnings' => []]; // Nenhum dado para sincronizar.
        }

        // Pré-carrega os dados de lookup antes do loop para evitar queries N+1.
        $allCourses = Course::with('internshipTypes')->get();
        $allUsers = User::select(['id', 'name'])
            ->whereNull('deactivated_at')
            ->get();
        $allCompanies = Company::all()->groupBy('legal_identifier');

        $processedCount = 0;
        $updateData = [];
        $rowErrors = [];
        $rowWarnings = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // A contagem de linhas começa em 2.

            // Se a coluna de controle 'BJ' (índice 60) estiver marcada com '1', pula a linha.
            if (isset($row[60]) && $row[60] == 1) {
                continue;
            }

            // --- MAPEAMENTO COMPLETO DE DADOS DA PLANILHA PARA VARIÁVEIS ---
            // Dados do Aluno / Responsável
            $emailEstagiario = $row[0] ?? null;  // Coluna B
            // $declaracaoCiente           = $row[1] ?? null;  // Coluna C
            $maiorDe18 = $row[2] ?? null;  // Coluna D
            $nomeResponsavelLegal = $row[3] ?? null;  // Coluna E
            $cpfResponsavelLegal = $this->formatDocument($row[4] ?? null, 11);  // Coluna F
            $parentescoResponsavelLegal = $row[5] ?? null;  // Coluna G
            $emailResponsavelLegal = $row[6] ?? null;  // Coluna H
            $nomeCurso = $row[7] ?? null;  // Coluna I
            $tipoEstagio = $row[8] ?? null;  // Coluna J
            $nomeCompletoEstagiario = $row[9] ?? null;  // Coluna K
            $matricula = $row[10] ?? null; // Coluna L
            $anoSemestre = $row[11] ?? null; // Coluna M
            // $dataNascimento = $row[12] ?? null; // Coluna N
            $rg = $row[13] ?? null; // Coluna O
            $rgOrgaoExpedidor = $row[14] ?? null; // Coluna P
            // $rgDataExpedicao = $row[15] ?? null; // Coluna Q
            $cpfEstagiario = $this->formatDocument($row[16] ?? null, 11); // Coluna R
            $telefoneEstagiario = $row[17] ?? null; // Coluna S
            $enderecoRuaEstagiario = $row[18] ?? null; // Coluna T
            $enderecoNumeroEstagiario = $row[19] ?? null; // Coluna U
            $enderecoBairroEstagiario = $row[20] ?? null; // Coluna V
            $cidadeEstagiario = $row[21] ?? null; // Coluna W
            $ufEstagiario = isset($row[22]) && $row[22] !== '' ? strtoupper(trim($row[22])) : null; // Coluna X
            $cepEstagiario = $row[23] ?? null; // Coluna Y
            $nomeOrientador = $row[24] ?? null; // Coluna Z

            // Dados da Empresa (Parte Concedente)
            $tipoDocumentoConcedente = $row[25] ?? null; // Coluna AA
            $cpfConcedente = $this->formatDocument($row[26] ?? null, 11); // Coluna AB
            $cnpjConcedente = $this->formatDocument($row[27] ?? null, 14); // Coluna AC
            $razaoSocialConcedente = $row[28] ?? null; // Coluna AD
            $telefoneConcedente = $row[29] ?? null; // Coluna AE
            $emailConcedente = $row[30] ?? null; // Coluna AF
            $enderecoRuaConcedente = $row[31] ?? null; // Coluna AG
            $enderecoNumeroConcedente = $row[32] ?? null; // Coluna AH
            $enderecoBairroConcedente = $row[33] ?? null; // Coluna AI
            $cidadeConcedente = $row[34] ?? null; // Coluna AJ
            $ufConcedente = isset($row[35]) && $row[35] !== '' ? strtoupper(trim($row[35])) : null; // Coluna AK
            $cepConcedente = $row[36] ?? null; // Coluna AL
            $nomeRepresentanteConcedente = $row[37] ?? null; // Coluna AM
            $cargoRepresentanteConcedente = $row[38] ?? null; // Coluna AN

            // Dados do Estágio e Supervisor
            $setorEstagio = $row[39] ?? null; // Coluna AO
            $nomeSupervisor = $row[40] ?? null; // Coluna AP
            $telefoneSupervisor = $row[41] ?? null; // Coluna AQ
            $emailSupervisor = $row[42] ?? null; // Coluna AR
            $cargoSupervisor = $row[43] ?? null; // Coluna AS
            $formacaoSupervisorPossui = $row[44] ?? null; // Coluna AT
            $formacaoDescricaoSupervisor = $row[45] ?? null; // Coluna AU
            $experienciaSupervisor = $row[46] ?? null; // Coluna AV
            $atividadesPrevistas = $row[47] ?? null; // Coluna AW

            // Carga Horária
            $horasDomingo = $row[48] ?? null; // Coluna AX
            $horasSegunda = $row[49] ?? null; // Coluna AY
            $horasTerca = $row[50] ?? null; // Coluna AZ
            $horasQuarta = $row[51] ?? null; // Coluna BA
            $horasQuinta = $row[52] ?? null; // Coluna BB
            $horasSexta = $row[53] ?? null; // Coluna BC
            $horasSabado = $row[54] ?? null; // Coluna BD

            // Detalhes Finais
            // $dataInicioEstagio = $row[55] ?? null; // Coluna BE
            $estagioRemunerado = $row[56] ?? null; // Coluna BF
            $valorBolsa = $row[57] ?? null; // Coluna BG
            $valorAuxilioTransporte = $row[58] ?? null; // Coluna BH
            $observacoes = $row[59] ?? null; // Coluna BI

            // Busca o curso pelo nome na coleção pré-carregada (sem query).
            $curso = $allCourses->first(function ($c) use ($nomeCurso) {
                return mb_stripos($c->name, $nomeCurso) !== false;
            });

            // Busca os dados do tipo de estágio relacionado ao curso.
            $internshipType = null;
            $internshipTypeName = null;
            $requiredHours = 0;
            $internshipTypeWeight = 1;

            if ($curso) {
                if ($tipoEstagio) {
                    // Tenta encontrar o tipo de estágio específico informado na coleção pré-carregada.
                    $internshipType = $curso->internshipTypes->first(function ($it) use ($tipoEstagio) {
                        return mb_stripos($it->name, $tipoEstagio) !== false;
                    });
                }

                // Se não encontrou ou não foi informado, usa o primeiro tipo de estágio do curso como padrão.
                if (! $internshipType) {
                    $internshipType = $curso->internshipTypes->first();
                }

                // Se um tipo de estágio foi encontrado, extrai seus dados.
                if ($internshipType) {
                    $internshipTypeName = $internshipType->name;
                    $requiredHours = $internshipType->required_hours;
                    $internshipTypeWeight = $internshipType->weight;
                } else {
                    $rowErrors[] = ['line' => $rowNumber, 'student' => $nomeCompletoEstagiario ?: 'Desconhecido', 'reason' => "Nenhum tipo de estágio encontrado para o curso '$nomeCurso'."];

                    continue;
                }
            } else {
                $rowErrors[] = ['line' => $rowNumber, 'student' => $nomeCompletoEstagiario ?: 'Desconhecido', 'reason' => "Curso '$nomeCurso' não encontrado."];

                continue;
            }

            // Busca o orientador pelo nome, tolerando pequenos erros de digitação (fuzzy search).
            $result = $fuzzySearch->fuzzyFind(User::class, 'name', $nomeOrientador);
            $orientador = null;
            $advisorWarning = '';

            if (! $result) {
                $rowErrors[] = ['line' => $rowNumber, 'student' => $nomeCompletoEstagiario ?: 'Desconhecido', 'reason' => "Orientador '$nomeOrientador' não encontrado."];

                continue;
            }

            $orientador = $result['entity'];

            // Se a correspondência não foi exata, adiciona um aviso nas observações.
            if (! $result['exact_match']) {
                $advisorWarning = $result['warning'];
            }

            if ($advisorWarning) {
                $observacoes = ($observacoes ? $observacoes."\n\n" : '').$advisorWarning;
                $rowWarnings[] = ['line' => $rowNumber, 'student' => $nomeCompletoEstagiario ?: 'Desconhecido', 'message' => $advisorWarning];
            }

            // Busca os dados da parte concedente (empresa) na coleção pré-carregada (sem query).
            $identificadorLegal = $cnpjConcedente ?? $cpfConcedente;
            $partesConcedentes = $allCompanies->get($identificadorLegal, collect());

            // Os dados da empresa vindos da planilha são descartados intencionalmente.
            // Quando a empresa é encontrada no banco, usamos os dados padronizados do cadastro local.
            // Quando não é encontrada, um aviso é adicionado às observações para seleção manual.
            $razaoSocialConcedente = null;
            $telefoneConcedente = null;
            $emailConcedente = null;
            $enderecoRuaConcedente = null;
            $enderecoNumeroConcedente = null;
            $enderecoBairroConcedente = null;
            $cidadeConcedente = null;
            $ufConcedente = null;
            $cepConcedente = null;
            $nomeRepresentanteConcedente = null;
            $cargoRepresentanteConcedente = null;
            $areaDeAtuacao = null;
            $registroConselhoProfissional = null;
            $numeroRegistroConselho = null;
            $numeroProcesso = null;

            // Trata os resultados da busca pela empresa.
            if ($partesConcedentes->count() === 1) {
                // Um resultado: usa os dados padronizados do banco de dados local.
                $parteConcedente = $partesConcedentes->first();
                $razaoSocialConcedente = $parteConcedente->name;
                $telefoneConcedente = $parteConcedente->phone;
                $emailConcedente = $parteConcedente->email;
                $enderecoRuaConcedente = $parteConcedente->address_street;
                $enderecoNumeroConcedente = $parteConcedente->address_number;
                $enderecoBairroConcedente = $parteConcedente->address_neighborhood;
                $cidadeConcedente = $parteConcedente->address_city;
                $ufConcedente = $parteConcedente->address_state;
                $cepConcedente = $parteConcedente->address_zip;
                $nomeRepresentanteConcedente = $parteConcedente->representative_name;
                $cargoRepresentanteConcedente = $parteConcedente->representative_role;
                $areaDeAtuacao = $parteConcedente->field_of_activity;
                $registroConselhoProfissional = $parteConcedente->professional_council ?? null;
                $numeroRegistroConselho = $parteConcedente->council_registration_number ?? null;
                $numeroProcesso = $parteConcedente->process_number ?? null;
            } elseif ($partesConcedentes->count() > 1) {
                // Múltiplos resultados: adiciona um aviso.
                $warningMsg = "Múltiplas empresas encontradas com o CNPJ/CPF {$identificadorLegal}. Seleção manual necessária.";
                $observacoes = ($observacoes ? $observacoes."\n\n" : '').'ATENÇÃO: '.$warningMsg;
                $rowWarnings[] = ['line' => $rowNumber, 'student' => $nomeCompletoEstagiario ?: 'Desconhecido', 'message' => $warningMsg];
            } else {
                // Nenhum resultado: adiciona um aviso.
                $nomeEmpresaForm = $row[28] ?? 'Não informado';
                $warningMsg = "Nenhuma empresa encontrada com o CNPJ/CPF {$identificadorLegal}. Cadastro da empresa necessário. Nome informado: {$nomeEmpresaForm}";
                $observacoes = ($observacoes ? $observacoes."\n\n" : '').'ATENÇÃO: '.$warningMsg;
                $rowWarnings[] = ['line' => $rowNumber, 'student' => $nomeCompletoEstagiario ?: 'Desconhecido', 'message' => $warningMsg];
            }

            // Inicializa as variáveis de data para evitar uso de variáveis indefinidas
            // quando as células correspondentes da planilha estão vazias.
            $dataNascimento = null;
            $rgDataExpedicao = null;
            $dataInicioEstagio = null;

            if ($row[12]) {
                try {
                    $dataNascimento = Carbon::createFromFormat('d/m/Y', $row[12])->startOfDay();
                } catch (\Exception $e) {
                    $rowErrors[] = ['line' => $rowNumber, 'student' => $nomeCompletoEstagiario ?: 'Desconhecido', 'reason' => 'Data de nascimento inválida.'];

                    continue;
                }
            }

            if ($row[15]) {
                try {
                    $rgDataExpedicao = Carbon::createFromFormat('d/m/Y', $row[15])->startOfDay();
                } catch (\Exception $e) {
                    $rowErrors[] = ['line' => $rowNumber, 'student' => $nomeCompletoEstagiario ?: 'Desconhecido', 'reason' => 'Data de expedição do RG inválida.'];

                    continue;
                }
            }

            if ($row[55]) {
                try {
                    $dataInicioEstagio = Carbon::createFromFormat('d/m/Y', $row[55])->startOfDay();
                } catch (\Exception $e) {
                    $rowErrors[] = ['line' => $rowNumber, 'student' => $nomeCompletoEstagiario ?: 'Desconhecido', 'reason' => 'Data de início do estágio inválida.'];

                    continue;
                }
            }

            // Valida e calcula a carga horária semanal.
            $weeklyHours = [
                (int) ($horasDomingo ?? 0),
                (int) ($horasSegunda ?? 0),
                (int) ($horasTerca ?? 0),
                (int) ($horasQuarta ?? 0),
                (int) ($horasQuinta ?? 0),
                (int) ($horasSexta ?? 0),
                (int) ($horasSabado ?? 0),
            ];

            $totalWeeklyHours = array_sum($weeklyHours);

            if ($totalWeeklyHours > 30) {
                $rowErrors[] = ['line' => $rowNumber, 'student' => $nomeCompletoEstagiario ?: 'Desconhecido', 'reason' => "Carga horária semanal excede o limite de 30 horas. Total informado: {$totalWeeklyHours} horas."];

                continue;
            }

            if ($totalWeeklyHours <= 0) {
                $rowErrors[] = ['line' => $rowNumber, 'student' => $nomeCompletoEstagiario ?: 'Desconhecido', 'reason' => 'A carga horária semanal deve ser maior que zero.'];

                continue;
            }

            // Calcula a data de fim do estágio com base na carga horária total e semanal.
            $dataFimEstagio = null;
            if ($dataInicioEstagio && $requiredHours > 0) {
                try {
                    $dataFimEstagio = InternshipEndDate::calculateInternshipEndDate(
                        $dataInicioEstagio,
                        $weeklyHours,
                        $requiredHours
                    );
                } catch (\Exception $e) {
                    $rowErrors[] = ['line' => $rowNumber, 'student' => $nomeCompletoEstagiario ?: 'Desconhecido', 'reason' => 'Erro ao calcular data de fim do estágio: '.$e->getMessage()];

                    continue;
                }
            }

            // Formata valores monetários e booleanos.
            $valorBolsa = ($valorBolsa === '' || $valorBolsa === null) ? null : str_replace(',', '.', $valorBolsa);
            $valorAuxilioTransporte = ($valorAuxilioTransporte === '' || $valorAuxilioTransporte === null) ? null : str_replace(',', '.', $valorAuxilioTransporte);
            $estagioRemunerado = strtolower($estagioRemunerado) === 'sim';
            $maiorDe18 = strtolower($maiorDe18) === 'sim';

            try {
                // Cria o registro de estágio no banco de dados.
                Internship::create([
                    // Dados do estudante
                    'student_name' => $nomeCompletoEstagiario,
                    'student_email' => $emailEstagiario,
                    'student_registration_number' => $matricula,
                    'student_year_semester' => $anoSemestre,
                    'student_birth_date' => $dataNascimento,
                    'student_is_adult' => $maiorDe18,
                    'student_rg' => $rg,
                    'student_rg_issuer' => $rgOrgaoExpedidor,
                    'student_rg_issue_date' => $rgDataExpedicao,
                    'student_cpf' => $cpfEstagiario,
                    'student_phone' => $telefoneEstagiario,
                    'student_address_street' => $enderecoRuaEstagiario,
                    'student_address_number' => $enderecoNumeroEstagiario,
                    'student_address_neighborhood' => $enderecoBairroEstagiario,
                    'student_address_city' => $cidadeEstagiario,
                    'student_address_state' => $ufEstagiario,
                    'student_address_zip' => $cepEstagiario,

                    // Dados do responsável legal
                    'legal_guardian_name' => $nomeResponsavelLegal,
                    'legal_guardian_cpf' => $cpfResponsavelLegal,
                    'legal_guardian_kinship' => $parentescoResponsavelLegal,
                    'legal_guardian_email' => $emailResponsavelLegal,

                    // Dados do estágio
                    'internship_type_name' => $internshipTypeName,
                    'internship_sector' => $setorEstagio,
                    'activities' => $atividadesPrevistas,
                    'start_date' => $dataInicioEstagio,
                    'end_date' => $dataFimEstagio,
                    'status' => InternshipStatus::PENDING,
                    'notes' => $observacoes,
                    'required_hours' => $requiredHours,
                    'internship_type_weight' => $internshipTypeWeight,
                    'great_value' => $internshipType->great_value,
                    'very_good_value' => $internshipType->very_good_value,
                    'good_value' => $internshipType->good_value,
                    'satisfactory_value' => $internshipType->satisfactory_value,
                    'unsatisfactory_value' => $internshipType->unsatisfactory_value,

                    // Dados do supervisor
                    'supervisor_name' => $nomeSupervisor,
                    'supervisor_phone' => $telefoneSupervisor,
                    'supervisor_email' => $emailSupervisor,
                    'supervisor_role' => $cargoSupervisor,
                    'supervisor_qualification' => $formacaoSupervisorPossui,
                    'supervisor_training' => $formacaoDescricaoSupervisor,
                    'supervisor_experience' => $experienciaSupervisor,

                    // Carga Horária
                    'hours_sunday' => $horasDomingo,
                    'hours_monday' => $horasSegunda,
                    'hours_tuesday' => $horasTerca,
                    'hours_wednesday' => $horasQuarta,
                    'hours_thursday' => $horasQuinta,
                    'hours_friday' => $horasSexta,
                    'hours_saturday' => $horasSabado,

                    // Remuneração
                    'is_remunerated' => $estagioRemunerado,
                    'grant_value' => $valorBolsa,
                    'transportation_allowance' => $valorAuxilioTransporte,

                    // Dados da parte concedente
                    'company_legal_identifier' => $identificadorLegal,
                    'company_name' => $razaoSocialConcedente,
                    'company_phone' => $telefoneConcedente,
                    'company_email' => $emailConcedente,
                    'company_address_street' => $enderecoRuaConcedente,
                    'company_address_number' => $enderecoNumeroConcedente,
                    'company_address_neighborhood' => $enderecoBairroConcedente,
                    'company_address_city' => $cidadeConcedente,
                    'company_address_state' => $ufConcedente,
                    'company_address_zip' => $cepConcedente,
                    'company_representative_name' => $nomeRepresentanteConcedente,
                    'company_representative_role' => $cargoRepresentanteConcedente,
                    'field_of_activity' => $areaDeAtuacao,
                    'professional_council' => $registroConselhoProfissional,
                    'council_registration_number' => $numeroRegistroConselho,
                    'process_number' => $numeroProcesso,

                    // Chaves Estrangeiras
                    'advisor_id' => $orientador->id,
                    'course_id' => $curso->id,
                ]);

                $processedCount++;

                // Adiciona a linha para o batchUpdate da planilha
                $updateRange = "'Respostas ao formulário 1'!BJ".$rowNumber;
                $updateData[] = new \Google_Service_Sheets_ValueRange([
                    'range' => $updateRange,
                    'values' => [[1]],
                ]);
            } catch (\Exception $e) {
                $rowErrors[] = ['line' => $rowNumber, 'student' => $nomeCompletoEstagiario ?: 'Desconhecido', 'reason' => 'Falha ao salvar no banco: '.$e->getMessage()];
            }
        }

        // Executa todas as atualizações na planilha em uma única requisição (Batch Update)
        if (! empty($updateData)) {
            $batchUpdateRequest = new \Google_Service_Sheets_BatchUpdateValuesRequest([
                'valueInputOption' => 'RAW',
                'data' => $updateData,
            ]);
            $service->spreadsheets_values->batchUpdate($spreadsheetId, $batchUpdateRequest);
        }

        return ['processed' => $processedCount, 'errors' => $rowErrors, 'warnings' => $rowWarnings];
    }

    /**
     * Sincroniza as avaliações de supervisores a partir de uma planilha do Google.
     *
     * Este método lê uma planilha de avaliações, mapeia cada coluna para um campo do
     * modelo `SupervisorEvaluation`, cria um novo registro no banco de dados e,
     * ao final, marca a linha como processada na planilha.
     *
     * @param  \App\Services\GoogleApiService  $googleService  Serviço para interagir com a API do Google.
     * @return int O número de avaliações processadas com sucesso.
     *
     * @throws \Exception Se o ID da planilha não estiver configurado.
     */
    private function syncSupervisorEvaluations(GoogleApiService $googleService): array
    {
        $spreadsheetId = config('services.google.sheets.supervisor_evaluation_id');

        if (! $spreadsheetId) {
            throw new \Exception('ID da planilha de avaliações não configurado');
        }

        $client = $googleService->getClient();
        $service = new \Google_Service_Sheets($client);

        $range = "'Respostas ao formulário 1'!A2:Z";

        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $rows = $response->getValues();

        if (empty($rows)) {
            return ['processed' => 0, 'errors' => [], 'warnings' => []]; // Nenhuma avaliação para sincronizar.
        }

        $processedCount = 0;
        $updateData = [];
        $evaluationsToInsert = [];
        $rowErrors = [];
        $rowWarnings = [];

        foreach ($rows as $index => $row) {
            // Se a coluna de controle 'Z' (índice 25) estiver marcada com '1', pula a linha.
            if (isset($row[25]) && $row[25] == 1) {
                continue;
            }

            // Mapeamento das colunas baseado no CSV
            // $timestamp = $row[0] ?? null;                           // Coluna A - Carimbo de data/hora
            $supervisorEmail = $row[1] ?? null;                     // Coluna B - Endereço de e-mail
            $studentName = $row[2] ?? null;                         // Coluna C - Nome do estagiário
            $supervisorName = $row[3] ?? null;                      // Coluna D - Seu nome completo
            $hasAcademicBackgroundRaw = $row[4] ?? null;            // Coluna E - Formação acadêmica na área?
            $completedWorkload = $row[5] ?? null;                   // Coluna F - Cumpriu a carga horária?

            // Extrai apenas "Sim" ou "Não" da resposta de formação acadêmica
            $hasAcademicBackground = $this->extractSimNao($hasAcademicBackgroundRaw);
            $trainingCourse = $row[6] ?? null;                      // Coluna G - Curso de formação
            $educationLevel = $row[7] ?? null;                      // Coluna H - Nível
            $jobRole1 = $row[8] ?? null;                            // Coluna I - Cargo/Função (opção 1)
            $jobRole2 = $row[9] ?? null;                            // Coluna J - Cargo/Função (opção 2)
            $experienceTime = $row[10] ?? null;                     // Coluna K - Tempo de experiência
            $performance = $row[11] ?? null;                        // Coluna L - 1. Rendimento
            $comprehension = $row[12] ?? null;                      // Coluna M - 2. Facilidade de compreensão
            $technicalKnowledge = $row[13] ?? null;                 // Coluna N - 3. Conhecimentos técnicos
            $organization = $row[14] ?? null;                       // Coluna O - 4. Organização
            $initiative = $row[15] ?? null;                         // Coluna P - 5. Iniciativa
            $attendance = $row[16] ?? null;                         // Coluna Q - 6. Assiduidade
            $discipline = $row[17] ?? null;                         // Coluna R - 7. Disciplina
            $sociability = $row[18] ?? null;                        // Coluna S - 8. Sociabilidade
            $cooperation = $row[19] ?? null;                        // Coluna T - 9. Cooperação
            $responsibility = $row[20] ?? null;                     // Coluna U - 10. Responsabilidade
            $considerations = $row[21] ?? null;                     // Coluna V - Considerações
            $suggestionsToInstitution = $row[22] ?? null;           // Coluna W - Sugestões à instituição
            $performanceIssues = $row[23] ?? null;                  // Coluna X - Aspectos que prejudicaram
            $otherObservations = $row[24] ?? null;                  // Coluna Y - Outras observações

            // Usa o cargo que estiver preenchido, com prioridade para o primeiro campo.
            $jobRole = ! empty($jobRole1) ? $jobRole1 : $jobRole2;

            $rowNumber = $index + 2;

            try {
                // Cria o registro individualmente para tratar falhas por linha
                SupervisorEvaluation::create([
                    'supervisor_email' => $supervisorEmail,
                    'student_name' => $studentName,
                    'supervisor_name' => $supervisorName,
                    'has_academic_background' => $hasAcademicBackground,
                    'completed_workload' => $completedWorkload,
                    'training_course' => $trainingCourse,
                    'education_level' => $educationLevel,
                    'job_role' => $jobRole,
                    'experience_time' => $experienceTime,
                    'performance' => $performance,
                    'comprehension' => $comprehension,
                    'technical_knowledge' => $technicalKnowledge,
                    'organization' => $organization,
                    'initiative' => $initiative,
                    'attendance' => $attendance,
                    'discipline' => $discipline,
                    'sociability' => $sociability,
                    'cooperation' => $cooperation,
                    'responsibility' => $responsibility,
                    'considerations' => $considerations,
                    'suggestions_to_institution' => $suggestionsToInstitution,
                    'performance_issues' => $performanceIssues,
                    'other_observations' => $otherObservations,
                ]);

                // Adiciona a linha para o batchUpdate da planilha se a inserção for bem sucedida
                $updateRange = "'Respostas ao formulário 1'!Z{$rowNumber}";
                $updateData[] = new \Google_Service_Sheets_ValueRange([
                    'range' => $updateRange,
                    'values' => [[1]],
                ]);

                $processedCount++;
            } catch (\Exception $e) {
                $rowErrors[] = ['line' => $rowNumber, 'student' => $studentName ?: 'Desconhecido', 'reason' => 'Falha ao salvar no banco: '.$e->getMessage()];
            }
        }

        // Executa todas as atualizações na planilha em uma única requisição (Batch Update)
        if (! empty($updateData)) {
            $batchUpdateRequest = new \Google_Service_Sheets_BatchUpdateValuesRequest([
                'valueInputOption' => 'RAW',
                'data' => $updateData,
            ]);
            $service->spreadsheets_values->batchUpdate($spreadsheetId, $batchUpdateRequest);
        }

        return ['processed' => $processedCount, 'errors' => $rowErrors, 'warnings' => $rowWarnings];
    }

    /**
     * Sincroniza a lista de orientadores/coordenadores com o formulário do Google.
     *
     * @param  \App\Services\GoogleApiService  $googleService  Serviço para interagir com a API do Google.
     * @return string O status da sincronização ('updated', 'up_to_date', 'disabled').
     *
     * @throws \Exception Se a pergunta não for encontrada no formulário ou houver falha na API.
     */
    private function syncAdvisorsToForm(GoogleApiService $googleService): string
    {
        $formId = config('services.google.forms.data_collection_id');
        $questionId = config('services.google.forms.advisors_question_id');

        if (! $formId || ! $questionId) {
            return 'disabled';
        }

        $client = $googleService->getClient();
        $service = new \Google_Service_Forms($client);

        // Busca o formulário para obter o index do item
        $form = $service->forms->get($formId);
        $itemIndex = null;
        $currentOptions = [];

        foreach ($form->getItems() as $index => $item) {
            if ($item->getItemId() == $questionId) {
                $itemIndex = $index;

                // Extrai as opções atuais para evitar atualizações redundantes
                $questionItem = $item->getQuestionItem();
                if ($questionItem && $questionItem->getQuestion() && $questionItem->getQuestion()->getChoiceQuestion()) {
                    $existingOptions = $questionItem->getQuestion()->getChoiceQuestion()->getOptions();
                    if ($existingOptions) {
                        foreach ($existingOptions as $opt) {
                            $currentOptions[] = $opt->getValue();
                        }
                    }
                }
                break;
            }
        }

        if ($itemIndex === null) {
            throw new \Exception('Pergunta de orientadores não encontrada no formulário.');
        }

        // Busca os orientadores/coordenadores ativos
        $advisors = User::whereNull('deactivated_at')
            ->whereIn('role', [\App\Enums\UserRole::ORIENTADOR, \App\Enums\UserRole::COORDENADOR])
            ->orderBy('name')
            ->pluck('name')
            ->toArray();

        if (empty($advisors)) {
            return 'disabled';
        }

        // Se a lista do formulário já for exatamente igual à lista do banco,
        // aborta a atualização para economizar cota e tempo da API.
        if ($currentOptions === $advisors) {
            return 'up_to_date';
        }

        $options = array_map(function ($name) {
            return ['value' => $name];
        }, $advisors);

        $updateItemRequest = new \Google_Service_Forms_Request([
            'updateItem' => new \Google_Service_Forms_UpdateItemRequest([
                'item' => new \Google_Service_Forms_Item([
                    'itemId' => $questionId,
                    'questionItem' => new \Google_Service_Forms_QuestionItem([
                        'question' => new \Google_Service_Forms_Question([
                            'choiceQuestion' => new \Google_Service_Forms_ChoiceQuestion([
                                'type' => 'DROP_DOWN',
                                'options' => $options,
                            ]),
                        ]),
                    ]),
                ]),
                'updateMask' => 'questionItem.question.choiceQuestion.options',
                'location' => new \Google_Service_Forms_Location([
                    'index' => $itemIndex,
                ]),
            ]),
        ]);

        $batchUpdateRequest = new \Google_Service_Forms_BatchUpdateFormRequest([
            'requests' => [$updateItemRequest],
        ]);

        $service->forms->batchUpdate($formId, $batchUpdateRequest);

        return 'updated';
    }

    /**
     * Formata um CPF ou CNPJ garantindo o número correto de dígitos.
     *
     * Este método auxiliar garante que CPFs tenham 11 dígitos e CNPJs tenham 14 dígitos,
     * preenchendo com zeros à esquerda quando necessário. Isso é importante porque
     * o Google Sheets pode remover zeros iniciais ao retornar valores numéricos.
     *
     * @param  string|null  $value  O valor a ser formatado.
     * @param  int  $length  O tamanho esperado (11 para CPF, 14 para CNPJ).
     * @return string|null O valor formatado ou null se vazio.
     */
    private function formatDocument(?string $value, int $length = 11): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Remove caracteres não numéricos
        $cleaned = preg_replace('/[^0-9]/', '', $value);

        // Preenche com zeros à esquerda até atingir o tamanho esperado
        return str_pad($cleaned, $length, '0', STR_PAD_LEFT);
    }

    /**
     * Extrai "Sim" ou "Não" da primeira palavra de uma string.
     *
     * Este método auxiliar é usado para normalizar respostas de formulários que podem
     * conter texto adicional (ex: "Sim, possui formação na área").
     *
     * @param  string|null  $text  O texto a ser analisado.
     * @return string|null "Sim", "Não" ou null se não for possível determinar.
     */
    private function extractSimNao(?string $text): ?string
    {
        if (empty($text)) {
            return null;
        }

        // Pega a primeira palavra da string.
        $firstWord = strtok(trim($text), ' ,');

        // Normaliza para minúsculas para uma comparação insensível a maiúsculas/minúsculas.
        $normalized = mb_strtolower($firstWord);

        if (str_starts_with($normalized, 'sim')) {
            return 'Sim';
        } elseif (str_starts_with($normalized, 'não') || str_starts_with($normalized, 'nao')) {
            return 'Não';
        }

        return null;
    }
}
