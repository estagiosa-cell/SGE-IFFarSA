<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        /**
         * Gate para verificar se o usuário é Administrador.
         */
        Gate::define('is-admin', function (User $user) {
            return $user->role === UserRole::ADMIN;
        });

        /**
         * Gate para verificar se o usuário é Coordenador.
         */
        Gate::define('is-coordenador', function (User $user) {
            return $user->role === UserRole::COORDENADOR;
        });

        /**
         * Gate para verificar se o usuário é Orientador.
         */
        Gate::define('is-orientador', function (User $user) {
            return $user->role === UserRole::ORIENTADOR;
        });

        Gate::define('view-internships', function (User $user) {
            return $user->can('is-coordenador') || $user->can('is-orientador');
        });
    }
}
