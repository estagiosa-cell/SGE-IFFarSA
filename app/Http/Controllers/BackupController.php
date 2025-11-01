<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

/**
 * Controlador responsável pela gestão de backups do sistema.
 *
 * Permite a visualização da página de backup e a criação de novos backups
 * da base de dados.
 */
class BackupController extends Controller
{
    /**
     * Mostra a view de backup.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function __invoke(Request $request)
    {
        return view('admin.backup');
    }

    /**
     * Cria um novo backup da base de dados e inicia o download.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
     */
    public function createBackup(Request $request)
    {
        try {
            // Define o nome do arquivo de backup com base na data e hora atuais.
            $fileName = 'backup-sge-' . now()->format('Y-m-d_H-i-s') . '.zip';

            // Define o diretório de armazenamento do backup.
            $directory = config('backup.backup.name');
            $filePath = $directory . '/' . $fileName;

            // Executa o comando Artisan para criar o backup, apenas do banco de dados.
            Artisan::call('backup:run', [
                '--only-db' => true,
                '--filename' => $fileName
            ]);

            // Obtém o disco de armazenamento configurado para os backups.
            $disk = Storage::disk(config('backup.backup.destination.disks')[0]);

            // Verifica se o arquivo de backup foi realmente criado.
            if (!$disk->exists($filePath)) {
                throw new \Exception('O arquivo de backup não foi encontrado após a execução: ' . $filePath);
            }

            // Inicia o download do arquivo de backup. (se aparecer erro da IDE no método download, saiba que está funcionando normalmente)
            return $disk->download($filePath);

        } catch (\Exception $e) {
            // Em caso de erro, redireciona de volta com uma mensagem de erro.
            return redirect()->back()
                ->with('message', 'Erro ao gerar o backup: ' . $e->getMessage())
                ->with('messageType', 'danger');
        }
    }
}