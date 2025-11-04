<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('is-admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->can('is-admin');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('is-admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->can('is-admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): Response|bool
    {
        if ($user->id === $model->id) {
            return Response::deny('Você não pode excluir sua própria conta.');
        }

        if ($model->advisedInternships()->exists()) {
            return Response::deny('Não é possível excluir um orientador com estágios vinculados.');
        }

        if ($model->coordinatedCourses()->exists()) {
            return Response::deny('Não é possível excluir um coordenador com cursos vinculados.');
        }

        return $user->can('is-admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->can('is-admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->can('is-admin');
    }

    /**
     * Determine whether the user can deactivate the model.
     */
    public function deactivate(User $user, User $model): Response|bool
    {
        if ($user->id === $model->id) {
            return Response::deny('Você não pode desativar sua própria conta.');
        }

        return $user->can('is-admin');
    }

    /**
     * Determine whether the user can reactivate the model.
     */
    public function reactivate(User $user, User $model): bool
    {
        return $user->can('is-admin');
    }
}
