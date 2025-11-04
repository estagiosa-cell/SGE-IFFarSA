<?php

namespace App\Policies;

use App\Models\User;

class BackupPolicy
{
    /**
     * Determina se o usuário pode visualizar a página de backup.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('is-admin');
    }

    /**
     * Determina se o usuário pode criar um backup.
     */
    public function create(User $user): bool
    {
        return $user->can('is-admin');
    }
}
