<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class GetGoogleFormIdsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:form-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Libera temporariamente a rota /idform para descobrir os IDs das perguntas do Google Forms enquanto o comando estiver rodando';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Ativa a flag no cache
        Cache::put('google_id_route_enabled', true);

        $this->info('=== Rota Temporária Liberada ===');
        $this->info('A rota para visualizar os IDs das perguntas do formulário está ATIVA.');
        $this->info('Acesse no seu navegador (logado como administrador):');
        $this->line(url('/idform')."\n");
        $this->warn('Pressione Ctrl+C para encerrar o comando e desativar a rota.');

        // Registra o tratador de sinal para remover o cache quando o usuário pressionar Ctrl+C
        $this->trap([SIGINT, SIGTERM], function () {
            Cache::forget('google_id_route_enabled');
            $this->info("\nComando encerrado. A rota /idform foi desativada por segurança.");
            exit(0);
        });

        // Mantém o comando rodando
        while (true) {
            sleep(1);
        }

        return 0;
    }
}
