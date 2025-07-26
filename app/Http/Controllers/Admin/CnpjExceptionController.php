<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CnpjExceptionRequest;
use App\Models\CnpjException;
use Illuminate\Support\Facades\Http;

class CnpjExceptionController extends Controller
{
    /**
     * Exibe uma lista das exceções de CNPJ.
     */
    public function index()
    {
        $exceptions = CnpjException::latest()->paginate(6);
        return view('admin.cnpj_exceptions.index', compact('exceptions'));
    }

    /**
     * Exibe o formulário para criar uma nova exceção de CNPJ.
     */
    public function create()
    {
        return view('admin.cnpj_exceptions.create');
    }

    /**
     * Armazena uma nova exceção de CNPJ no banco de dados.
     */
    public function store(CnpjExceptionRequest $request)
    {
        $validated = $request->validated();
        $cnpjMatriz = $validated['cnpj_matriz'];

        // Consulta a BrasilAPI para obter a razão social
        $response = Http::timeout(10)->get("https://brasilapi.com.br/api/cnpj/v1/{$cnpjMatriz}");
        $razaoSocial = $response->json()['razao_social'] ?? 'Não foi possível obter a Razão Social';

        CnpjException::create([
            'cnpj_matriz' => $cnpjMatriz,
            'razao_social' => $razaoSocial,
        ]);

        return redirect()->route('admin.cnpj-exceptions.index')
            ->with('message', 'Exceção de CNPJ criada com sucesso.')
            ->with('messageType', 'success');
    }

    /**
     * Exibe o formulário para editar uma exceção de CNPJ específica.
     */
    public function edit(string $id)
    {
        $cnpjException = CnpjException::findOrFail($id);
        return view('admin.cnpj_exceptions.edit', compact('cnpjException'));
    }

    /**
     * Atualiza uma exceção de CNPJ no banco de dados.
     */
    public function update(CnpjExceptionRequest $request, string $id)
    {
        $validated = $request->validated();
        $cnpjMatriz = $validated['cnpj_matriz'];

        $cnpjException = CnpjException::findOrFail($id);

        // Consulta a BrasilAPI para obter a razão social atualizada
        $response = Http::timeout(10)->get("https://brasilapi.com.br/api/cnpj/v1/{$cnpjMatriz}");
        $razaoSocial = $response->json()['razao_social'] ?? 'Não foi possível obter a Razão Social';

        $cnpjException->update([
            'cnpj_matriz' => $cnpjMatriz,
            'razao_social' => $razaoSocial,
        ]);

        return redirect()->route('admin.cnpj-exceptions.index')
            ->with('message', 'Exceção de CNPJ atualizada com sucesso.')
            ->with('messageType', 'success');
    }

    /**
     * Remove uma exceção de CNPJ do banco de dados.
     */
    public function destroy(string $id)
    {
        $cnpjException = CnpjException::findOrFail($id);
        $cnpjException->delete();

        return redirect()->route('admin.cnpj-exceptions.index')
            ->with('message', 'Exceção de CNPJ removida com sucesso.')
            ->with('messageType', 'success');
    }
}
