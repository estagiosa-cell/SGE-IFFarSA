<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GoogleApiService;
use Illuminate\Http\Request;

class SyncDataController extends Controller
{
    /**
     * Sincroniza os dados com a planilha do Google
     */
    public function __invoke(Request $request, GoogleApiService $googleService)
    {
        try {
            // Lógica de sincronização aqui
            // 1. Acessar Google Sheets
            // 2. Processar dados
            // 3. Salvar no banco

            return redirect()->route('admin.dashboard')
                ->with('message', 'Dados sincronizados com sucesso!')
                ->with('messageType', 'success');
        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')
                ->with('message', 'Erro na sincronização: ' . $e->getMessage())
                ->with('messageType', 'danger');
        }
    }
}
