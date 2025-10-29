<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    /**
     * Mostra a view de backup.
     */
    public function __invoke(Request $request)
    {
        return view('admin.backup');
    }

    /**
     * Cria e inicia o download do backup.
     */
    public function createBackup(Request $request)
    {
        try {
            $fileName = 'backup-sge-' . now()->format('Y-m-d_H-i-s') . '.zip';
            $directory = config('backup.backup.name');
            $filePath = $directory . '/' . $fileName;

            Artisan::call('backup:run', [
                '--only-db' => true,
                '--filename' => $fileName
            ]);

            $disk = Storage::disk(config('backup.backup.destination.disks')[0]);

            if (!$disk->exists($filePath)) {
                throw new \Exception('O arquivo de backup não foi encontrado após a execução: ' . $filePath);
            }

            return $disk->download($filePath);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('message', 'Erro ao gerar o backup: ' . $e->getMessage())
                ->with('messageType', 'danger');
        }
    }
}