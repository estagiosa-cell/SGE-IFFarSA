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
use Illuminate\Http\Request;

class SyncDataController extends Controller
{
    /**
     * Sincroniza os dados com as planilhas do Google (estágios e avaliações)
     */
    public function __invoke(Request $request, GoogleApiService $googleService, FuzzySearchService $fuzzySearch)
    {
        $messages = [];
        $hasSuccess = false;

        // Sincroniza dados de estágios
        try {
            $internshipsCount = $this->syncInternshipsData($googleService, $fuzzySearch);
            if ($internshipsCount > 0) {
                $messages[] = "{$internshipsCount} novo(s) estágio(s) sincronizado(s)";
                $hasSuccess = true;
            }
        } catch (\Exception $e) {
            $messages[] = 'Estágios: '.$e->getMessage();
        }

        // Sincroniza avaliações de supervisores
        try {
            $evaluationsCount = $this->syncSupervisorEvaluations($googleService);
            if ($evaluationsCount > 0) {
                $messages[] = "{$evaluationsCount} nova(s) avaliação(ões) sincronizada(s)";
                $hasSuccess = true;
            }
        } catch (\Exception $e) {
            $messages[] = 'Avaliações: '.$e->getMessage();
        }

        $finalMessage = empty($messages)
            ? 'Nenhum dado novo para sincronizar.'
            : implode(' | ', $messages);

        $messageType = $hasSuccess ? 'success' : 'info';

        return redirect()->route('admin.dashboard')
            ->with('message', $finalMessage)
            ->with('messageType', $messageType);
    }

    /**
     * Sincroniza os dados de estágios com a planilha do Google
     */
    private function syncInternshipsData(GoogleApiService $googleService, FuzzySearchService $fuzzySearch): int
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
            return 0; // Nenhum dado para sincronizar
        }

        $processedCount = 0;

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            // se a coluna BJ (índice 60) estiver marcada como 1, pula a linha
            if (isset($row[60]) && $row[60] == 1) {
                continue;
            }

            // --- MAPEAMENTO COMPLETO DE DADOS ---
            // Dados do Aluno / Responsável
            $emailEstagiario = $row[0] ?? null;  // Coluna B
            // $declaracaoCiente           = $row[1] ?? null;  // Coluna C
            $maiorDe18 = $row[2] ?? null;  // Coluna D
            $nomeResponsavelLegal = $row[3] ?? null;  // Coluna E
            $cpfResponsavelLegal = $row[4] ?? null;  // Coluna F
            $parentescoResponsavelLegal = $row[5] ?? null;  // Coluna G
            $emailResponsavelLegal = $row[6] ?? null;  // Coluna H
            $nomeCurso = $row[7] ?? null;  // Coluna I
            $tipoEstagio = $row[8] ?? null;  // Coluna J
            $nomeCompletoEstagiario = $row[9] ?? null;  // Coluna K
            $matricula = $row[10] ?? null; // Coluna L
            $anoSemestre = $row[11] ?? null; // Coluna M
            $dataNascimento = $row[12] ?? null; // Coluna N
            $rg = $row[13] ?? null; // Coluna O
            $rgOrgaoExpedidor = $row[14] ?? null; // Coluna P
            $rgDataExpedicao = $row[15] ?? null; // Coluna Q
            $cpfEstagiario = $row[16] ?? null; // Coluna R
            $telefoneEstagiario = $row[17] ?? null; // Coluna S
            $enderecoRuaEstagiario = $row[18] ?? null; // Coluna T
            $enderecoNumeroEstagiario = $row[19] ?? null; // Coluna U
            $enderecoBairroEstagiario = $row[20] ?? null; // Coluna V
            $cidadeEstagiario = $row[21] ?? null; // Coluna W
            $ufEstagiario = $row[22] ?? null; // Coluna X
            $cepEstagiario = $row[23] ?? null; // Coluna Y
            $nomeOrientador = $row[24] ?? null; // Coluna Z

            // Dados da Empresa (Parte Concedente)
            $tipoDocumentoConcedente = $row[25] ?? null; // Coluna AA
            $cpfConcedente = $row[26] ?? null; // Coluna AB
            $cnpjConcedente = $row[27] ?? null; // Coluna AC
            $razaoSocialConcedente = $row[28] ?? null; // Coluna AD
            $telefoneConcedente = $row[29] ?? null; // Coluna AE
            $emailConcedente = $row[30] ?? null; // Coluna AF
            $enderecoRuaConcedente = $row[31] ?? null; // Coluna AG
            $enderecoNumeroConcedente = $row[32] ?? null; // Coluna AH
            $enderecoBairroConcedente = $row[33] ?? null; // Coluna AI
            $cidadeConcedente = $row[34] ?? null; // Coluna AJ
            $ufConcedente = $row[35] ?? null; // Coluna AK
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
            $dataInicioEstagio = $row[55] ?? null; // Coluna BE
            $estagioRemunerado = $row[56] ?? null; // Coluna BF
            $valorBolsa = $row[57] ?? null; // Coluna BG
            $valorAuxilioTransporte = $row[58] ?? null; // Coluna BH
            $observacoes = $row[59] ?? null; // Coluna BI

            // busca o curso
            $curso = Course::whereLike('name', $nomeCurso)->first();

            // busca os dados do tipo de estágio relacionado ao curso
            $internshipType = null;
            $internshipTypeName = null;
            $requiredHours = 0; // valor padrão
            $internshipTypeWeight = 1; // valor padrão

            if ($curso) {
                if ($tipoEstagio) {
                    // Primeiro tenta encontrar o tipo de estágio específico dentro dos tipos do curso
                    $internshipType = $curso->internshipTypes()->whereLike('name', $tipoEstagio)->first();
                }

                // Se não encontrou o tipo específico ou não foi informado, pega o primeiro tipo de estágio do curso
                if (! $internshipType) {
                    $internshipType = $curso->internshipTypes()->first();
                }

                // Se encontrou um tipo de estágio, copia os dados dele
                if ($internshipType) {
                    $internshipTypeName = $internshipType->name;
                    $requiredHours = $internshipType->required_hours;
                    $internshipTypeWeight = $internshipType->weight;
                } else {
                    throw new \Exception("Nenhum tipo de estágio encontrado para o curso '$nomeCurso' do estagiário '$nomeCompletoEstagiario' na linha $rowNumber.");
                }
            } else {
                throw new \Exception("Curso '$nomeCurso' não encontrado para o estagiário '$nomeCompletoEstagiario' na linha $rowNumber.");
            }

            // busca orientador (tolera erro de digitação)
            $result = $fuzzySearch->fuzzyFind(User::class, 'name', $nomeOrientador);
            $orientador = null;
            $advisorWarning = '';

            if (! $result) {
                throw new \Exception("Orientador '$nomeOrientador' não encontrado para o estagiário '$nomeCompletoEstagiario' na linha $rowNumber.");
            }

            $orientador = $result['entity'];

            if (! $result['exact_match']) {
                $advisorWarning = $result['warning'];
            }

            // se achou o orientador por similaridade, registra isso
            if ($advisorWarning) {
                $observacoes = ($observacoes ? $observacoes."\n\n" : '').$advisorWarning;

            }
            // busca os dados da parte concedente
            $identificadorLegal = $cnpjConcedente ?? $cpfConcedente;
            $partesConcedentes = Company::where('legal_identifier', $identificadorLegal)->get();

            // RF-I02.3: Tratamento dos Resultados da Busca
            if ($partesConcedentes->count() === 1) {
                // RF-I02.3.1: Um resultado - usa dados padronizados da base local
                $parteConcedente = $partesConcedentes->first();
                $identificadorLegal = $parteConcedente->legal_identifier;
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
                // RF-I02.3.2: Múltiplos resultados - status Pendente + anotação
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
                $observacoes = ($observacoes ? $observacoes."\n\n" : '').
                    "ATENÇÃO: Múltiplas empresas encontradas com o CNPJ/CPF {$identificadorLegal}. Seleção manual necessária.";
            } else {
                // RF-I02.3.3: Nenhum resultado - campos vazios + status Pendente
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

                $observacoes = ($observacoes ? $observacoes."\n\n" : '').
                    "ATENÇÃO: Nenhuma empresa encontrada com o CNPJ/CPF {$identificadorLegal}. Cadastro da empresa necessário.";
            }

            // formata as datas com validação
            $dataNascimento = null;
            $rgDataExpedicao = null;
            $dataInicioEstagio = null;

            if ($row[12]) {
                try {
                    $dataNascimento = Carbon::createFromFormat('d/m/Y', $row[12])->startOfDay();
                } catch (\Exception $e) {
                    throw new \Exception("Data de nascimento inválida para o estagiário '$nomeCompletoEstagiario' na linha $rowNumber.");
                }
            }

            if ($row[15]) {
                try {
                    $rgDataExpedicao = Carbon::createFromFormat('d/m/Y', $row[15])->startOfDay();
                } catch (\Exception $e) {
                    throw new \Exception("Data de expedição do RG inválida para o estagiário '$nomeCompletoEstagiario' na linha $rowNumber.");
                }
            }

            if ($row[55]) {
                try {
                    $dataInicioEstagio = Carbon::createFromFormat('d/m/Y', $row[55])->startOfDay();
                } catch (\Exception $e) {
                    throw new \Exception("Data de início do estágio inválida para o estagiário '$nomeCompletoEstagiario' na linha $rowNumber.");
                }
            }

            // Validação e cálculo da carga horária semanal
            $weeklyHours = [
                (int) ($horasDomingo ?? 0),      // Domingo
                (int) ($horasSegunda ?? 0),      // Segunda
                (int) ($horasTerca ?? 0),        // Terça
                (int) ($horasQuarta ?? 0),       // Quarta
                (int) ($horasQuinta ?? 0),       // Quinta
                (int) ($horasSexta ?? 0),        // Sexta
                (int) ($horasSabado ?? 0),       // Sábado
            ];

            $totalWeeklyHours = array_sum($weeklyHours);

            // Validação: máximo 30 horas semanais
            if ($totalWeeklyHours > 30) {
                throw new \Exception("A carga horária semanal do estagiário '$nomeCompletoEstagiario' na linha $rowNumber excede o limite de 30 horas. Total informado: {$totalWeeklyHours} horas.");
            }

            // Validação: deve ter pelo menos 1 hora semanal
            if ($totalWeeklyHours <= 0) {
                throw new \Exception("A carga horária semanal do estagiário '$nomeCompletoEstagiario' na linha $rowNumber deve ser maior que zero.");
            }

            // Cálculo da data de fim do estágio
            $dataFimEstagio = null;
            if ($dataInicioEstagio && $requiredHours > 0) {
                try {
                    $dataFimEstagio = InternshipEndDate::calculateInternshipEndDate(
                        $dataInicioEstagio,
                        $weeklyHours,
                        $requiredHours
                    );
                } catch (\Exception $e) {
                    throw new \Exception("Erro ao calcular data de fim do estágio para '$nomeCompletoEstagiario' na linha $rowNumber: ".$e->getMessage());
                }
            }

            $valorBolsa = ($valorBolsa === '' || $valorBolsa === null) ? null : str_replace(',', '.', $valorBolsa);
            $valorAuxilioTransporte = ($valorAuxilioTransporte === '' || $valorAuxilioTransporte === null) ? null : str_replace(',', '.', $valorAuxilioTransporte);
            $estagioRemunerado = strtolower($estagioRemunerado) === 'sim' ? true : false;
            $maiorDe18 = strtolower($maiorDe18) === 'sim' ? true : false;
            // salvar no banco de dados
            Internship::create([

                // dados estudante
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

                // dados responsavel legal
                'legal_guardian_name' => $nomeResponsavelLegal,
                'legal_guardian_cpf' => $cpfResponsavelLegal,
                'legal_guardian_kinship' => $parentescoResponsavelLegal,
                'legal_guardian_email' => $emailResponsavelLegal,

                // dados do estágio
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

                // dados supervisor
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

                // dados da parte concedente
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

            $updateRange = "'Respostas ao formulário 1'!BJ".$rowNumber;
            $values = [[1]];
            $body = new \Google_Service_Sheets_ValueRange(['values' => $values]);
            $params = ['valueInputOption' => 'RAW'];

            $service->spreadsheets_values->update($spreadsheetId, $updateRange, $body, $params);
        }

        return $processedCount;
    }

    /**
     * Sincroniza as avaliações de supervisores com a planilha do Google
     */
    private function syncSupervisorEvaluations(GoogleApiService $googleService): int
    {
        $spreadsheetId = config('services.google.sheets.supervisor_evaluation_id');

        if (! $spreadsheetId) {
            throw new \Exception('ID da planilha de avaliações não configurado');
        }

        $client = $googleService->getClient();
        $service = new \Google_Service_Sheets($client);

        // Range incluindo coluna Z para controle de sincronização
        $range = "'Respostas ao formulário 1'!A2:Z";

        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $rows = $response->getValues();

        if (empty($rows)) {
            return 0; // Nenhuma avaliação para sincronizar
        }

        $processedCount = 0;
        $skippedCount = 0;

        foreach ($rows as $index => $row) {
            // Verifica se já foi sincronizado (coluna Z = 1)
            if (isset($row[25]) && $row[25] == 1) {
                $skippedCount++;

                continue;
            }

            // Mapeamento das colunas baseado no CSV
            $timestamp = $row[0] ?? null;                           // Coluna A - Carimbo de data/hora
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

            // Usa o cargo que estiver preenchido (prioriza jobRole1, depois jobRole2)
            $jobRole = ! empty($jobRole1) ? $jobRole1 : $jobRole2;

            // Cria o registro da avaliação
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

            // Marca como sincronizado na planilha (coluna Z = 1)
            $rowNumber = $index + 2; // +2 porque começa em A2
            $updateRange = "'Respostas ao formulário 1'!Z{$rowNumber}";
            $values = [[1]];
            $body = new \Google_Service_Sheets_ValueRange([
                'values' => $values,
            ]);
            $params = ['valueInputOption' => 'RAW'];
            $service->spreadsheets_values->update($spreadsheetId, $updateRange, $body, $params);

            $processedCount++;
        }

        return $processedCount;
    }

    /**
     * Extrai apenas "Sim" ou "Não" da primeira palavra de uma resposta
     */
    private function extractSimNao(?string $text): ?string
    {
        if (empty($text)) {
            return null;
        }

        // Remove espaços extras e pega a primeira palavra
        $firstWord = strtok(trim($text), ' ,');

        // Normaliza para maiúsculas/minúsculas
        $normalized = mb_strtolower($firstWord);

        // Retorna "Sim" ou "Não" baseado na primeira palavra
        if (str_starts_with($normalized, 'sim')) {
            return 'Sim';
        } elseif (str_starts_with($normalized, 'não') || str_starts_with($normalized, 'nao')) {
            return 'Não';
        }

        return null;
    }
}
