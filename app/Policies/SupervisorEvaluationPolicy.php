<?php

namespace App\Policies;

use App\Models\SupervisorEvaluation;
use App\Models\User;

class SupervisorEvaluationPolicy
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
    public function view(User $user, SupervisorEvaluation $supervisorEvaluation): bool
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
    public function update(User $user, SupervisorEvaluation $supervisorEvaluation): bool
    {
        return $user->can('is-admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SupervisorEvaluation $supervisorEvaluation): bool
    {
        return $user->can('is-admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SupervisorEvaluation $supervisorEvaluation): bool
    {
        return $user->can('is-admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SupervisorEvaluation $supervisorEvaluation): bool
    {
        return $user->can('is-admin');
    }

    /**
     * Determine whether the user can associate the evaluation to an internship.
     */
    public function associate(User $user, SupervisorEvaluation $supervisorEvaluation): bool
    {
        return $user->can('is-admin');
    }
}
