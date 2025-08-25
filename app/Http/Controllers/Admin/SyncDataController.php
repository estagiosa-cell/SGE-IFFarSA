<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GoogleApiService;
use Illuminate\Http\Request;
use App\Models\Internship;
use Google_Service_Sheets_ValueRange;

class SyncDataController extends Controller
{
    /**
     * Sincroniza os dados com a planilha do Google
     */
    public function __invoke(Request $request, GoogleApiService $googleService)
    {
        $spreadsheetId = config('services.google.sheet_id');

        if (!$spreadsheetId) {
            return redirect()->route('admin.dashboard')
                ->with('message', 'O ID da planilha do Google não está configurado.')
                ->with('messageType', 'danger');
        }

        $client = $googleService->getClient();
        $service = new \Google_Service_Sheets($client);

        $range = "'Respostas ao formulário 1'!B2:BJ";

        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $rows = $response->getValues();

        if (empty($rows)) {
            return redirect()->route('admin.dashboard')
                ->with('message', 'Nenhum dado novo para sincronizar encontrado na planilha.')
                ->with('messageType', 'info');
        }

        $processedCount = 0;

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            // se a coluna BJ (índice 60) estiver marcada como 'sincronizado', pula a linha
            if (isset($row[60]) && $row[60] == 'sincronizado') {
                continue;
            }

            // --- MAPEAMENTO COMPLETO DE DADOS ---
            // Dados do Aluno / Responsável
            $emailEstagiario           = $row[0] ?? null;  // Coluna B
            $declaracaoCiente          = $row[1] ?? null;  // Coluna C
            $maiorDe18                 = $row[2] ?? null;  // Coluna D
            $nomeResponsavelLegal      = $row[3] ?? null;  // Coluna E
            $cpfResponsavelLegal       = $row[4] ?? null;  // Coluna F
            $grauParentesco            = $row[5] ?? null;  // Coluna G
            $emailResponsavelLegal     = $row[6] ?? null;  // Coluna H
            $curso                     = $row[7] ?? null;  // Coluna I
            $tipoEstagio               = $row[8] ?? null;  // Coluna J
            $nomeCompletoEstagiario    = $row[9] ?? null;  // Coluna K
            $matricula                 = $row[10] ?? null; // Coluna L
            $anoSemestre               = $row[11] ?? null; // Coluna M
            $dataNascimento            = $row[12] ?? null; // Coluna N
            $rg                        = $row[13] ?? null; // Coluna O
            $rgOrgaoExpedidor          = $row[14] ?? null; // Coluna P
            $rgDataExpedicao           = $row[15] ?? null; // Coluna Q
            $cpfEstagiario             = $row[16] ?? null; // Coluna R
            $telefoneEstagiario        = $row[17] ?? null; // Coluna S
            $enderecoRuaEstagiario     = $row[18] ?? null; // Coluna T
            $enderecoNumeroEstagiario  = $row[19] ?? null; // Coluna U
            $enderecoBairroEstagiario  = $row[20] ?? null; // Coluna V
            $cidadeEstagiario          = $row[21] ?? null; // Coluna W
            $ufEstagiario              = $row[22] ?? null; // Coluna X
            $cepEstagiario             = $row[23] ?? null; // Coluna Y
            $nomeOrientador            = $row[24] ?? null; // Coluna Z

            // Dados da Empresa (Parte Concedente)
            $tipoDocumentoConcedente   = $row[25] ?? null; // Coluna AA
            $cpfConcedente             = $row[26] ?? null; // Coluna AB
            $cnpjConcedente            = $row[27] ?? null; // Coluna AC
            $razaoSocialConcedente     = $row[28] ?? null; // Coluna AD
            $telefoneConcedente        = $row[29] ?? null; // Coluna AE
            $emailConcedente           = $row[30] ?? null; // Coluna AF
            $enderecoRuaConcedente     = $row[31] ?? null; // Coluna AG
            $enderecoNumeroConcedente  = $row[32] ?? null; // Coluna AH
            $enderecoBairroConcedente  = $row[33] ?? null; // Coluna AI
            $cidadeConcedente          = $row[34] ?? null; // Coluna AJ
            $ufConcedente              = $row[35] ?? null; // Coluna AK
            $cepConcedente             = $row[36] ?? null; // Coluna AL
            $nomeRepresentanteConcedente = $row[37] ?? null; // Coluna AM
            $cargoRepresentanteConcedente = $row[38] ?? null; // Coluna AN

            // Dados do Estágio e Supervisor
            $setorEstagio              = $row[39] ?? null; // Coluna AO
            $nomeSupervisor            = $row[40] ?? null; // Coluna AP
            $telefoneSupervisor        = $row[41] ?? null; // Coluna AQ
            $emailSupervisor           = $row[42] ?? null; // Coluna AR
            $cargoSupervisor           = $row[43] ?? null; // Coluna AS
            $formacaoSupervisorPossui  = $row[44] ?? null; // Coluna AT
            $formacaoDescricaoSupervisor = $row[45] ?? null; // Coluna AU
            $experienciaSupervisor     = $row[46] ?? null; // Coluna AV
            $atividadesPrevistas       = $row[47] ?? null; // Coluna AW

            // Carga Horária
            $horasDomingo              = $row[48] ?? null; // Coluna AX
            $horasSegunda              = $row[49] ?? null; // Coluna AY
            $horasTerca                = $row[50] ?? null; // Coluna AZ
            $horasQuarta               = $row[51] ?? null; // Coluna BA
            $horasQuinta               = $row[52] ?? null; // Coluna BB
            $horasSexta                = $row[53] ?? null; // Coluna BC
            $horasSabado               = $row[54] ?? null; // Coluna BD

            // Detalhes Finais
            $dataInicioEstagio         = $row[55] ?? null; // Coluna BE
            $estagioRemunerado         = $row[56] ?? null; // Coluna BF
            $valorBolsa                = $row[57] ?? null; // Coluna BG
            $valorAuxilioTransporte    = $row[58] ?? null; // Coluna BH
            $observacoes               = $row[59] ?? null; // Coluna BI




            // salvar no banco de dados

            $processedCount++;


            $updateRange = "'Respostas ao formulário 1'!BJ" . $rowNumber;
            $values = [['sincronizado']];
            $body = new Google_Service_Sheets_ValueRange(['values' => $values]);
            $params = ['valueInputOption' => 'RAW'];

            $service->spreadsheets_values->update($spreadsheetId, $updateRange, $body, $params);
        }

        $message = ($processedCount == 0)
            ? 'Nenhum registro novo para sincronizar.'
            : $processedCount . ' novos registros foram sincronizados com sucesso!';

        $messageType = ($processedCount == 0) ? 'info' : 'success';

        return redirect()->route('admin.dashboard')
            ->with('message', $message)
            ->with('messageType', $messageType);
    }
}
