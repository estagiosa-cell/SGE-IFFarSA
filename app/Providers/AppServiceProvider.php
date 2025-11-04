<?php

namespace App\Providers;

use App\Services\FuzzySearchService;
use App\Services\GoogleApiService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider principal da aplicação.
 *
 * Responsável por registrar serviços no container de injeção de dependências
 * e configurar comportamentos globais da aplicação.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra serviços no container da aplicação.
     *
     * Os serviços registrados como singleton são instanciados apenas uma vez
     * e reutilizados em toda a aplicação.
     */
    public function register(): void
    {
        // Registra o serviço de integração com Google APIs como singleton.
        $this->app->singleton(GoogleApiService::class, function ($app) {
            return new GoogleApiService;
        });

        // Registra o serviço de busca fuzzy como singleton.
        $this->app->singleton(FuzzySearchService::class, function ($app) {
            return new FuzzySearchService;
        });
    }

    /**
     * Inicializa serviços e configurações globais da aplicação.
     */
    public function boot(): void
    {
        // Previne lazy loading de relacionamentos Eloquent em ambiente de desenvolvimento.
        // Isso ajuda a identificar problemas de N+1 queries durante o desenvolvimento.
        Model::preventLazyLoading(! app()->isProduction());

        // Configura a paginação para usar o framework Bootstrap 5.
        Paginator::useBootstrapFive();
    }
}
