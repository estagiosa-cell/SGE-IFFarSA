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

            // Lógica para salvar no banco de dados.



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
