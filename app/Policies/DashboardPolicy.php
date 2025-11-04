<?php

namespace App\Policies;

use App\Models\User;

class DashboardPolicy
{
    /**
     * Determina se o usuário pode visualizar o dashboard administrativo.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('is-admin');
    }
}
