<?php

namespace App\Http\Controllers\Admin;

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

            // Salva o ID do documento no banco
            $internship->update(['google_docs_id' => $documentId]);

            return redirect()->back()
                ->with('message', 'Documento gerado com sucesso!')
                ->with('messageType', 'success');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('message', 'Erro ao gerar documento: '.$e->getMessage())
                ->with('messageType', 'error');
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
            '{{MATRICULA}}' => $internship->student_registration_number, // Corrigido
            '{{TEL_ALUNO}}' => $internship->student_phone,
            '{{NASC_ALUNO}}' => $internship->student_birth_date ? $internship->student_birth_date->format('d/m/Y') : '',
            '{{CPF_ALUNO}}' => $internship->student_cpf,
            '{{RG_ALUNO}}' => $internship->student_rg,
            '{{ORGAO_EMISSOR}}' => $internship->student_rg_issuer,
            '{{DATA_EMISSAO}}' => $internship->student_rg_issue_date ? $internship->student_rg_issue_date->format('d/m/Y') : '', // Corrigido
            '{{RUA_ALUNO}}' => $internship->student_address_street, // Corrigido
            '{{NUMCASA_ALUNO}}' => (string) $internship->student_address_number, // Corrigido
            '{{BAIRRO_ALUNO}}' => $internship->student_address_neighborhood,
            '{{CIDADE_ALUNO}}' => $internship->student_address_city,
            '{{ESTADO_ALUNO}}' => $internship->student_address_state, // Corrigido
            '{{CEP_ALUNO}}' => $internship->student_address_zip,

            // Dados da empresa/concedente
            '{{NOME_EMPRESA}}' => $internship->company_name,
            '{{CNPJ_OU_CPF}}' => $internship->company_legal_identifier,
            '{{EMAIL_EMPRESA}}' => $internship->company_email,
            '{{TEL_EMPRESA}}' => $internship->company_phone,
            '{{AREA_ATUA}}' => $internship->field_of_activity, // Corrigido
            '{{AREA}}' => $internship->internship_sector, // Corrigido
            '{{RUA_EMPRESA}}' => $internship->company_address_street,
            '{{NUMLOCAL_EMPRESA}}' => (string) $internship->company_address_number,
            '{{BAIRRO_EMPRESA}}' => $internship->company_address_neighborhood,
            '{{CIDADE_EMPRESA}}' => $internship->company_address_city,
            '{{ESTADO_EMPRESA}}' => $internship->company_address_state,
            '{{CEP_EMPRESA}}' => $internship->company_address_zip, // Corrigido

            '{{REPRESENTANTE}}' => $internship->company_representative_name,
            '{{CARGO_REP}}' => $internship->company_representative_role,

            // Dados do estágio - Convertidos explicitamente para string
            '{{HORAS CURSO}}' => (string) ($internship->required_hours ?? 0),
            '{{HORAS_CURSO_EXTENSO}}' => $this->horasExtenso((int) ($internship->required_hours ?? 0)),
            '{{INICIO}}' => $internship->start_date ? $internship->start_date->format('d/m/Y') : '',
            '{{DATA_TERMINO}}' => $internship->end_date ? $internship->end_date->format('d/m/Y') : '',
            '{{MAIOR_CARGA}}' => (string) $dailyHours,
            '{{MAIOR_CARGA_EXTENSO}}' => $this->horasExtenso($dailyHours),
            '{{HORAS_SEMANAIS}}' => (string) $weeklyHours,
            '{{HORAS_SEMANAIS_EXTENSO}}' => $this->horasExtenso($weeklyHours),
            '{{ESPECIAL}}' => '', // Não sei para que serve

            // Dados do orientador e supervisor
            '{{ORIENTADOR}}' => $internship->advisor->name,
            '{{SUPERVISOR}}' => $internship->supervisor_name,
            '{{TEL_SUPERVISOR}}' => $internship->supervisor_phone,
            '{{EMAIL_SUPERVISOR}}' => $internship->supervisor_email,

            '{{CAMPO_RESPONSAVEL_LEGAL}}' => ! $internship->student_is_adult === "Não" ? "ASSINATURA DIGITAL\n_____________________\n{$internship->legal_guardian_name}\nResponsável Legal do Estagiário" : '',
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

    private function horasExtenso(int $horas): string
    {
        if ($horas === 0) {
            return 'zero horas';
        }

        if ($horas === 1) {
            return 'uma hora';
        }

        $texto = $this->numeroParaTexto($horas);

        return $texto.' horas';
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
        }

        // Para números muito grandes, retorna o número mesmo
        return (string) $numero;
    }
}
