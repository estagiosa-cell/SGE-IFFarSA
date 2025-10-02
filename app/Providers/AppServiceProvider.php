<?php

namespace App\Providers;

use App\Services\FuzzySearchService;
use App\Services\GoogleApiService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GoogleApiService::class, function ($app) {
            return new GoogleApiService;
        });

        $this->app->singleton(FuzzySearchService::class, function ($app) {
            return new FuzzySearchService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        Paginator::useBootstrapFive();
    }
}
