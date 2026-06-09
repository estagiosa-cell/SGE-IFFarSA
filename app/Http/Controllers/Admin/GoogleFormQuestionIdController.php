<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GoogleApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

/**
 * Controlador auxiliar para descobrir os IDs das perguntas do Google Forms.
 * Funciona apenas quando a flag no Cache é ativada pelo comando do Artisan.
 */
class GoogleFormQuestionIdController extends Controller
{
    public function __invoke(GoogleApiService $googleService): JsonResponse
    {
        if (! Cache::get('google_id_route_enabled')) {
            abort(404, 'Rota temporariamente desativada. Rode o comando "php artisan google:form-ids" para ativá-la.');
        }

        $formId = config('services.google.forms.data_collection_id');

        if (! $formId) {
            return response()->json(['error' => 'ID do formulário não configurado no .env'], 400);
        }

        try {
            $client = $googleService->getClient();
            $service = new \Google_Service_Forms($client);
            $form = $service->forms->get($formId);

            $itemsList = [];
            foreach ($form->getItems() as $item) {
                $itemsList[] = [
                    'title' => $item->getTitle() ?: '(Sem título)',
                    'id' => $item->getItemId(),
                ];
            }

            return response()->json([
                'form_title' => $form->info->title ?? 'Sem Título',
                'items' => $itemsList,
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
