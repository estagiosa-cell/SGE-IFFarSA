<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider de autorização e autenticação.
 *
 * Define Gates (portões de autorização) para controlar o acesso
 * baseado nos perfis/papéis dos usuários.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Registra serviços no container.
     */
    public function register(): void
    {
        //
    }

    /**
     * Inicializa os Gates de autorização da aplicação.
     *
     * Define as regras de permissão baseadas nos papéis dos usuários.
     */
    public function boot(): void
    {
        /**
         * Gate para verificar se o usuário é Administrador.
         *
         * @param  \App\Models\User  $user
         * @return bool
         */
        Gate::define('is-admin', function (User $user) {
            return $user->role === UserRole::ADMIN;
        });

        /**
         * Gate para verificar se o usuário é Coordenador.
         *
         * @param  \App\Models\User  $user
         * @return bool
         */
        Gate::define('is-coordenador', function (User $user) {
            return $user->role === UserRole::COORDENADOR;
        });

        /**
         * Gate para verificar se o usuário é Orientador.
         *
         * @param  \App\Models\User  $user
         * @return bool
         */
        Gate::define('is-orientador', function (User $user) {
            return $user->role === UserRole::ORIENTADOR;
        });

        /**
         * Gate para verificar se o usuário é Direção de Ensino.
         *
         * @param  \App\Models\User  $user
         * @return bool
         */
        Gate::define('is-direcao-ensino', function (User $user) {
            return $user->role === UserRole::DIRECAO_ENSINO;
        });

        /**
         * Gate para verificar se o usuário pode visualizar estágios.
         *
         * Permite acesso a coordenadores, orientadores e direção de ensino.
         *
         * @param  \App\Models\User  $user
         * @return bool
         */
        Gate::define('view-internships', function (User $user) {
            return $user->can('is-coordenador') || $user->can('is-orientador') || $user->can('is-direcao-ensino');
        });
    }
}
