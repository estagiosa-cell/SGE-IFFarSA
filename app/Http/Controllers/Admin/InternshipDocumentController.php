<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InternshipStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateInternshipDocumentRequest;
use App\Models\Internship;
use App\Services\GoogleApiService;

/**
 * Controlador para gerar documentos de estágio usando a API do Google Docs.
 *
 * Este controlador é "invokable" e sua única responsabilidade é orquestrar
 * a criação de um documento no Google Drive a partir de um template,
 * preenchendo-o com os dados de um estágio específico.
 */
class InternshipDocumentController extends Controller
{
    /**
     * Manipula a requisição para gerar um documento de estágio.
     *
     * @param  \App\Http\Requests\GenerateInternshipDocumentRequest  $request  A requisição HTTP validada.
     * @param  int  $internshipId  O ID do estágio para o qual o documento será gerado.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(GenerateInternshipDocumentRequest $request, $internshipId)
    {
        // Encontra o estágio ou falha, carregando relacionamentos necessários.
        $internship = Internship::with(['advisor', 'course'])->findOrFail($internshipId);
        $documentType = $request->validated()['document_type'];

        try {
            $currentDateTime = now()->format('d/m/Y H:i');

            // Seleciona o template e o título do documento com base no tipo solicitado.
            $documentConfig = match ($documentType) {
                'termo-compromisso' => [
                    'template_id' => config('services.google.docs.templates.termo_compromisso_padrao'),
                    'title' => "Termo de Compromisso de Estágio - {$internship->student_name} - {$currentDateTime}",
                ],
                'termo-emater-rs' => [
                    'template_id' => config('services.google.docs.templates.termo_emater_rs'),
                    'title' => "Termo de Compromisso EMATER/RS - {$internship->student_name} - {$currentDateTime}",
                ],
                'termo-seduc' => [
                    'template_id' => config('services.google.docs.templates.termo_seduc'),
                    'title' => "Termo de Compromisso SEDUC - {$internship->student_name} - {$currentDateTime}",
                ],
                'rescisao' => [
                    'template_id' => config('services.google.docs.templates.rescisao'),
                    'title' => "Termo de Rescisão de Estágio - {$internship->student_name} - {$currentDateTime}",
                ],
                'credenciamento' => [
                    'template_id' => config('services.google.docs.templates.credenciamento'),
                    'title' => "Termo de Compromisso de Estágio | Credenciamento - {$internship->student_name} - {$currentDateTime}",
                ],
                'termo-aditivo-terceira-clausula' => [
                    'template_id' => config('services.google.docs.templates.termo_aditivo_terceira_clausula'),
                    'title' => "Termo Aditivo - Cláusula Terceira - {$internship->student_name} - {$currentDateTime}",
                ],
                default => throw new \InvalidArgumentException("Tipo de documento '{$documentType}' não suportado.")
            };

            // Validação específica para o documento de credenciamento.
            if ($documentType === 'credenciamento' && empty($internship->process_number)) {
                throw new \Exception('Não é possível gerar o documento de credenciamento: o número do processo não foi informado.');
            }

            // Verifica se o ID do template está configurado no ambiente.
            if (! $documentConfig['template_id']) {
                throw new \Exception('ID do template do Termo de Compromisso não configurado no .env');
            }

            // Inicializa os serviços da API do Google.
            $googleService = new GoogleApiService;
            $client = $googleService->getClient();

            $driveService = new \Google_Service_Drive($client);
            $docsService = new \Google_Service_Docs($client);

            // Tenta acessar o template no Google Drive para verificar sua existência e permissões.
            try {
                $driveService->files->get($documentConfig['template_id']);
            } catch (\Google_Service_Exception $e) {
                if ($e->getCode() === 404) {
                    throw new \Exception("Template não encontrado no Google Drive. Verifique se o ID '{$documentConfig['template_id']}' está correto e se o documento existe e está compartilhado com a conta google utilizada.");
                }
                throw new \Exception('Erro ao acessar template no Google Drive: '.$e->getMessage());
            }

            // Cria uma cópia do arquivo de template no Google Drive.
            $copy = new \Google_Service_Drive_DriveFile;
            $copy->setName($documentConfig['title']);

            // Se uma pasta de destino estiver configurada, move a cópia para lá.
            if (config('services.google.drive_folder_id')) {
                $copy->setParents([config('services.google.drive_folder_id')]);
            }

            try {
                $copiedFile = $driveService->files->copy($documentConfig['template_id'], $copy);
                $documentId = $copiedFile->getId();
            } catch (\Google_Service_Exception $e) {
                throw new \Exception('Erro ao criar cópia do template: '.$e->getMessage());
            }

            // Busca todos os dados para substituição no documento.
            $replacements = $this->getReplacements($internship);

            // Prepara as requisições de substituição de texto para a API do Google Docs.
            $requests = [];
            foreach ($replacements as $placeholder => $value) {
                // Garante que todos os valores sejam strings para evitar erros na API.
                $stringValue = is_null($value) ? '' : (string) $value;

                $requests[] = [
                    'replaceAllText' => [
                        'containsText' => [
                            'text' => $placeholder,
                            'matchCase' => false,
                        ],
                        'replaceText' => $stringValue,
                    ],
                ];
            }

            // Executa a substituição em lote se houver placeholders a serem preenchidos.
            if (! empty($requests)) {
                $batchUpdateRequest = new \Google_Service_Docs_BatchUpdateDocumentRequest([
                    'requests' => $requests,
                ]);

                $docsService->documents->batchUpdate($documentId, $batchUpdateRequest);
            }

            // Salva o ID do documento gerado no banco de dados e atualiza o status do estágio.
            $internship->update([
                'google_docs_id' => $documentId,
                'status' => InternshipStatus::AWAITING_SIGNATURE->value,
            ]);

            return redirect()->back()
                ->with('message', 'Documento gerado com sucesso!')
                ->with('messageType', 'success');

        } catch (\Exception $e) {
            // Captura qualquer exceção durante o processo e retorna uma mensagem de erro.
            return redirect()->back()
                ->with('message', 'Erro ao gerar documento: '.$e->getMessage())
                ->with('messageType', 'danger');
        }
    }

    /**
     * Coleta e formata todos os dados de um estágio para substituição em um template.
     *
     * @param  \App\Models\Internship  $internship  O estágio contendo os dados.
     * @return array Um array associativo de `[placeholder => valor]`.
     */
    private function getReplacements($internship): array
    {
        // Calcula a carga horária diária (maior valor entre os dias da semana).
        $dailyHours = max(
            (int) ($internship->hours_sunday ?? 0),
            (int) ($internship->hours_monday ?? 0),
            (int) ($internship->hours_tuesday ?? 0),
            (int) ($internship->hours_wednesday ?? 0),
            (int) ($internship->hours_thursday ?? 0),
            (int) ($internship->hours_friday ?? 0),
            (int) ($internship->hours_saturday ?? 0)
        );

        // Calcula a carga horária semanal total.
        $weeklyHours = $internship->getTotalWeeklyHours();

        return [
            // Dados do aluno
            '{{NOME_ALUNO}}' => $internship->student_name,
            '{{CURSO}}' => $internship->course->name,
            '{{ANO/SEMESTRE}}' => $internship->student_year_semester,
            '{{EMAIL_ALUNO}}' => $internship->student_email,
            '{{MATRICULA}}' => $internship->student_registration_number,
            '{{TEL_ALUNO}}' => $internship->student_phone,
            '{{NASC_ALUNO}}' => $internship->student_birth_date ? $internship->student_birth_date->format('d/m/Y') : '',
            '{{CPF_ALUNO}}' => $internship->student_cpf,
            '{{RG_ALUNO}}' => $internship->student_rg,
            '{{ORGAO_EMISSOR}}' => $internship->student_rg_issuer,
            '{{DATA_EMISSAO}}' => $internship->student_rg_issue_date ? $internship->student_rg_issue_date->format('d/m/Y') : '',
            '{{RUA_ALUNO}}' => $internship->student_address_street,
            '{{NUMCASA_ALUNO}}' => (string) $internship->student_address_number,
            '{{BAIRRO_ALUNO}}' => $internship->student_address_neighborhood,
            '{{CIDADE_ALUNO}}' => $internship->student_address_city,
            '{{ESTADO_ALUNO}}' => $internship->student_address_state,
            '{{CEP_ALUNO}}' => $internship->student_address_zip,

            // Dados da empresa/concedente
            '{{NOME_EMPRESA}}' => $internship->company_name,
            '{{CNPJ_OU_CPF}}' => $internship->company_legal_identifier,
            '{{EMAIL_EMPRESA}}' => $internship->company_email,
            '{{TEL_EMPRESA}}' => $internship->company_phone,
            '{{AREA_ATUA}}' => $internship->field_of_activity,
            '{{AREA}}' => $internship->internship_sector,
            '{{RUA_EMPRESA}}' => $internship->company_address_street,
            '{{NUMLOCAL_EMPRESA}}' => (string) $internship->company_address_number,
            '{{BAIRRO_EMPRESA}}' => $internship->company_address_neighborhood,
            '{{CIDADE_EMPRESA}}' => $internship->company_address_city,
            '{{ESTADO_EMPRESA}}' => $internship->company_address_state,
            '{{CEP_EMPRESA}}' => $internship->company_address_zip,
            '{{REPRESENTANTE}}' => $internship->company_representative_name,
            '{{CARGO_REP}}' => $internship->company_representative_role,

            // Dados do estágio
            '{{HORAS CURSO}}' => (string) ($internship->required_hours ?? 0),
            '{{HORAS_CURSO_EXTENSO}}' => $this->numeroParaTexto((int) ($internship->required_hours ?? 0)),
            '{{INICIO}}' => $internship->start_date ? $internship->start_date->format('d/m/Y') : '',
            '{{DATA_TERMINO}}' => $internship->end_date ? $internship->end_date->format('d/m/Y') : '',
            '{{MAIOR_CARGA}}' => (string) $dailyHours,
            '{{MAIOR_CARGA_EXTENSO}}' => $this->numeroParaTexto($dailyHours),
            '{{HORAS_SEMANAIS}}' => (string) $weeklyHours,
            '{{HORAS_SEMANAIS_EXTENSO}}' => $this->numeroParaTexto($weeklyHours),
            '{{ESPECIAL}}' => $this->formatarCampoEspecial($internship),

            // Dados do orientador e supervisor
            '{{ORIENTADOR}}' => $internship->advisor->name,
            '{{SUPERVISOR}}' => $internship->supervisor_name,
            '{{TEL_SUPERVISOR}}' => $internship->supervisor_phone,
            '{{EMAIL_SUPERVISOR}}' => $internship->supervisor_email,

            // Campos compostos
            '{{CAMPO_RESPONSAVEL_LEGAL}}' => $this->formatarCampoResponsavelLegal($internship),
            '{{ATIVIDADES}}' => $internship->activities ?? '',
            '{{CREDENCIAMENTO}}' => $internship->process_number ?? '',
            '{{DATA_ATUAL}}' => now()->locale('pt_BR')->translatedFormat('d \d\e F \d\e Y'),
        ];
    }

    /**
     * Converte um número inteiro para sua representação por extenso em português.
     * Suporta números até 999.999.
     *
     * @param  int  $numero  O número a ser convertido.
     * @return string O número por extenso.
     */
    private function numeroParaTexto(int $numero): string
    {
        if ($numero === 0) {
            return 'zero';
        }
        // Casos especiais para concordância de gênero.
        if ($numero === 1) {
            return 'uma';
        }
        if ($numero === 2) {
            return 'duas';
        }

        $unidades = [
            '', 'um', 'dois', 'três', 'quatro', 'cinco', 'seis', 'sete', 'oito', 'nove',
            'dez', 'onze', 'doze', 'treze', 'quatorze', 'quinze', 'dezesseis',
            'dezessete', 'dezoito', 'dezenove',
        ];

        $dezenas = [
            '', '', 'vinte', 'trinta', 'quarenta', 'cinquenta',
            'sessenta', 'setenta', 'oitenta', 'noventa',
        ];

        $centenas = [
            '', 'cento', 'duzentos', 'trezentos', 'quatrocentos', 'quinhentos',
            'seiscentos', 'setecentos', 'oitocentos', 'novecentos',
        ];

        if ($numero < 20) {
            return $unidades[$numero];
        }

        if ($numero < 100) {
            $dezena = intval($numero / 10);
            $unidade = $numero % 10;

            if ($unidade === 0) {
                return $dezenas[$dezena];
            }

            return $dezenas[$dezena].' e '.$unidades[$unidade];
        }

        if ($numero === 100) {
            return 'cem';
        }

        if ($numero < 1000) {
            $centena = intval($numero / 100);
            $resto = $numero % 100;

            if ($resto === 0) {
                return $centenas[$centena];
            }

            return $centenas[$centena].' e '.$this->numeroParaTexto($resto);
        }

        if ($numero < 1000000) {
            $milhares = intval($numero / 1000);
            $resto = $numero % 1000;

            $resultado = '';

            if ($milhares === 1) {
                $resultado = 'mil';
            } else {
                $resultado = $this->numeroParaTexto($milhares).' mil';
            }

            if ($resto > 0) {
                // Regra para evitar "mil e cem" -> "mil e cem" e "mil duzentos" -> "mil e duzentos"
                if ($resto < 100 || $resto % 100 === 0) {
                    $resultado .= ' e '.$this->numeroParaTexto($resto);
                } else {
                    $resultado .= ' '.$this->numeroParaTexto($resto);
                }
            }

            return $resultado;
        }

        // Para números muito grandes, retorna o próprio número como string.
        return (string) $numero;
    }

    /**
     * Formata o bloco de texto do responsável legal para o documento.
     *
     * @param  \App\Models\Internship  $internship  O estágio.
     * @return string O texto formatado ou uma string vazia.
     *
     * @throws \Exception Se o estagiário for menor de idade e os dados do responsável estiverem incompletos.
     */
    private function formatarCampoResponsavelLegal($internship): string
    {
        // Se o estudante for menor de idade, os dados do responsável são obrigatórios.
        if (! $internship->student_is_adult) {
            if (! empty($internship->legal_guardian_name) &&
                ! empty($internship->legal_guardian_cpf) &&
                ! empty($internship->legal_guardian_kinship)) {

                return "____________________________________________\n".
                       "Responsável Legal (para estagiário menor de 18 anos):\n".
                       "Nome: {$internship->legal_guardian_name}\n".
                       "CPF: {$internship->legal_guardian_cpf}\n".
                       "Grau de parentesco: {$internship->legal_guardian_kinship}";
            } else {
                // Lança uma exceção se os dados estiverem faltando, impedindo a geração do documento.
                throw new \Exception('Não é possível gerar o documento: faltam dados do responsável legal para o estagiário menor de idade. Verifique se o nome, CPF e grau de parentesco estão preenchidos.');
            }
        }

        // Se o estudante for maior de idade, retorna uma string vazia.
        return '';
    }

    /**
     * Formata a cláusula de remuneração do estágio para o documento.
     *
     * @param  \App\Models\Internship  $internship  O estágio.
     * @return string A cláusula formatada.
     */
    private function formatarCampoEspecial($internship): string
    {
        if ($internship->is_remunerated) {
            // Texto para estágio remunerado.
            $valorBolsa = $internship->grant_value ?? 0;
            $auxilioTransporte = $internship->transportation_allowance ?? 0;

            $valorBolsaExtenso = $this->numeroParaTextoMonetario($valorBolsa);
            $auxilioTransporteExtenso = $this->numeroParaTextoMonetario($auxilioTransporte);

            return '§1º Nesse Estágio obrigatório, o valor da bolsa e do auxílio-transporte diário serão, respectivamente, de R$ '.
                   number_format($valorBolsa, 2, ',', '.').' ('.$valorBolsaExtenso.') e R$ '.
                   number_format($auxilioTransporte, 2, ',', '.').' ('.$auxilioTransporteExtenso.').';
        } else {
            // Texto para estágio não remunerado.
            return '§1º Neste Estágio Obrigatório o estudante não receberá bolsa de estágio ou auxílio transporte.';
        }
    }

    /**
     * Converte um valor monetário (float) para sua representação por extenso em português.
     *
     * @param  float  $valor  O valor monetário.
     * @return string O valor por extenso (ex: "cem reais e cinquenta centavos").
     */
    private function numeroParaTextoMonetario(float $valor): string
    {
        if ($valor == 0) {
            return 'zero reais';
        }

        $inteiro = (int) $valor;
        $centavos = (int) round(($valor - $inteiro) * 100);

        $textoInteiro = $this->numeroParaTexto($inteiro);

        // Trata o plural de "real".
        if ($inteiro == 1) {
            $resultado = $textoInteiro.' real';
        } else {
            $resultado = $textoInteiro.' reais';
        }

        if ($centavos > 0) {
            $textoCentavos = $this->numeroParaTexto($centavos);
            // Trata o plural de "centavo".
            if ($centavos == 1) {
                $resultado .= ' e '.$textoCentavos.' centavo';
            } else {
                $resultado .= ' e '.$textoCentavos.' centavos';
            }
        }

        return $resultado;
    }
}
