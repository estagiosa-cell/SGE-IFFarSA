<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InternshipStatus;
use App\Http\Controllers\Controller;
use App\Models\Internship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InternshipDocumentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $estagio)
    {
        // Carregar o modelo Internship com suas relações
        $internship = Internship::with(['advisor', 'course'])->findOrFail($estagio);
        $tipo = $request->input('document_type');

        // Verificar permissões
        if (! Auth::user()->can('is-admin') && ! Auth::user()->can('is-coordenador')) {
            return redirect()->back()
                ->with('message', 'Você não tem permissão para gerar documentos.')
                ->with('messageType', 'error');
        }

        try {
            $documentId = match ($tipo) {
                'termo-compromisso' => $this->gerarTermoDeCompromissoPadrao($internship),
                default => throw new \InvalidArgumentException("Tipo de documento '{$tipo}' não suportado.")
            };

            // Salva o ID do documento no banco e atualiza o status
            $internship->update([
                'google_docs_id' => $documentId,
                'status' => InternshipStatus::AWAITING_SIGNATURE->value,
            ]);

            return redirect()->back()
                ->with('message', 'Documento gerado com sucesso!')
                ->with('messageType', 'success');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('message', 'Erro ao gerar documento: '.$e->getMessage())
                ->with('messageType', 'danger');
        }
    }

    private function gerarTermoDeCompromissoPadrao($internship)
    {
        $templateId = config('services.google.template_termo_compromisso_id');
        $folderId = config('services.google.drive_folder_id');

        if (! $templateId) {
            throw new \Exception('ID do template do Termo de Compromisso não configurado no .env');
        }

        // Inicializar serviços Google
        $googleService = app(\App\Services\GoogleApiService::class);
        $client = $googleService->getClient();

        $driveService = new \Google_Service_Drive($client);
        $docsService = new \Google_Service_Docs($client);

        // Verificar se o template existe antes de tentar copiar
        try {
            $driveService->files->get($templateId);
        } catch (\Google_Service_Exception $e) {
            if ($e->getCode() === 404) {
                throw new \Exception("Template não encontrado no Google Drive. Verifique se o ID '{$templateId}' está correto e se o documento existe e está compartilhado com a conta de serviço.");
            }
            throw new \Exception('Erro ao acessar template no Google Drive: '.$e->getMessage());
        }

        // Criar cópia do template
        $newDocName = $internship->student_name.'_'.$internship->course->name.'_Termo_Compromisso_'.date('d_m_Y_H:i:s');
        $copy = new \Google_Service_Drive_DriveFile;
        $copy->setName($newDocName);

        if ($folderId) {
            $copy->setParents([$folderId]);
        }

        try {
            $copiedFile = $driveService->files->copy($templateId, $copy);
            $documentId = $copiedFile->getId();
        } catch (\Google_Service_Exception $e) {
            throw new \Exception('Erro ao criar cópia do template: '.$e->getMessage());
        }

        // Calcular carga horária diária (maior valor dos dias da semana)
        $dailyHours = max(
            (int) $internship->hours_sunday ?? 0,
            (int) $internship->hours_monday ?? 0,
            (int) $internship->hours_tuesday ?? 0,
            (int) $internship->hours_wednesday ?? 0,
            (int) $internship->hours_thursday ?? 0,
            (int) $internship->hours_friday ?? 0,
            (int) $internship->hours_saturday ?? 0
        );

        // Calcular carga horária semanal (soma de todos os dias)
        $weeklyHours = $internship->getTotalWeeklyHours();

        // Preparar dados para substituição
        $replacements = [
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

            // Dados do estágio - Convertidos explicitamente para string
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

            '{{CAMPO_RESPONSAVEL_LEGAL}}' => $this->formatarCampoResponsavelLegal($internship),
            '{{ATIVIDADES}}' => $internship->activities ?? '',
        ];

        // Executar substituições no documento
        $requests = [];
        foreach ($replacements as $placeholder => $value) {
            // Garantir que todos os valores sejam strings
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

        if (! empty($requests)) {
            $batchUpdateRequest = new \Google_Service_Docs_BatchUpdateDocumentRequest([
                'requests' => $requests,
            ]);

            $docsService->documents->batchUpdate($documentId, $batchUpdateRequest);
        }

        return $documentId;
    }

    private function numeroParaTexto(int $numero): string
    {
        if ($numero === 0) {
            return 'zero';
        }
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
                if ($resto < 100) {
                    $resultado .= ' e '.$this->numeroParaTexto($resto);
                } else {
                    $resultado .= ' '.$this->numeroParaTexto($resto);
                }
            }

            return $resultado;
        }

        // Para números muito grandes, retorna o número mesmo
        return (string) $numero;
    }

    /**
     * Formata o campo de responsável legal para o documento
     */
    private function formatarCampoResponsavelLegal($internship): string
    {
        // Se o estudante NÃO é adulto (ou seja, é menor de idade)
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
                throw new \Exception('Não é possível gerar o documento: faltam dados do responsável legal para o estagiário menor de idade. Verifique se o nome, CPF e grau de parentesco estão preenchidos.');
            }
        }

        // Se o estudante é adulto, não precisa de responsável
        return '';
    }

    /**
     * Formata o campo especial para remuneração no documento
     */
    private function formatarCampoEspecial($internship): string
    {
        if ($internship->is_remunerated) {
            // Estágio remunerado
            $valorBolsa = $internship->grant_value ?? 0;
            $auxilioTransporte = $internship->transportation_allowance ?? 0;

            $valorBolsaExtenso = $this->numeroParaTextoMonetario($valorBolsa);
            $auxilioTransporteExtenso = $this->numeroParaTextoMonetario($auxilioTransporte);

            return '§1º Nesse Estágio obrigatório, o valor da bolsa e do auxílio-transporte diário serão, respectivamente, de R$ '.
                   number_format($valorBolsa, 2, ',', '.').' ('.$valorBolsaExtenso.') e R$ '.
                   number_format($auxilioTransporte, 2, ',', '.').' ('.$auxilioTransporteExtenso.').';
        } else {
            // Estágio não remunerado
            return '§1º Neste Estágio Obrigatório o estudante não receberá bolsa de estágio ou auxílio transporte.';
        }
    }

    /**
     * Converte número monetário para texto por extenso
     */
    private function numeroParaTextoMonetario(float $valor): string
    {
        if ($valor == 0) {
            return 'zero reais';
        }

        $inteiro = (int) $valor;
        $centavos = (int) round(($valor - $inteiro) * 100);

        $textoInteiro = $this->numeroParaTexto($inteiro);

        if ($inteiro == 1) {
            $resultado = $textoInteiro.' real';
        } else {
            $resultado = $textoInteiro.' reais';
        }

        if ($centavos > 0) {
            $textoCentavos = $this->numeroParaTexto($centavos);
            if ($centavos == 1) {
                $resultado .= ' e '.$textoCentavos.' centavo';
            } else {
                $resultado .= ' e '.$textoCentavos.' centavos';
            }
        }

        return $resultado;
    }
}
