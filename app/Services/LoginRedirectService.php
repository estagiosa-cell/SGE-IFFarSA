<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;


class LoginRedirectService
{
    /**
     * Determina o nome da rota para onde o utilizador deve ser redirecionado.
     *
     * @param Authenticatable $user
     * @return string
     */
    public function getRedirectRoute(Authenticatable $user): string
    {
        if (Gate::allows('is-admin', $user)) {
            return 'dashboard';
        }

        if (Gate::allows('is-coordenador', $user)) {
            return 'estagios';
        }

        if (Gate::allows('is-orientador', $user)) {
            return 'estagios';
        }

        return 'home';
    }
}
