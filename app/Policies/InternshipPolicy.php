<?php

namespace App\Policies;

use App\Models\Internship;
use App\Models\User;

class InternshipPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admins, orientadores e coordenadores podem ver a lista de estágios
        return $user->can('is-admin')
            || $user->can('is-orientador')
            || $user->can('is-coordenador');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Internship $internship): bool
    {
        // Admins podem ver qualquer estágio
        if ($user->can('is-admin')) {
            return true;
        }

        // Orientadores podem ver estágios que eles orientam
        if ($user->can('is-orientador') && $internship->advisor_id === $user->id) {
            return true;
        }

        // Coordenadores podem ver estágios dos cursos que coordenam ou que eles próprios orientam
        if ($user->can('is-coordenador')) {
            $coordinatedCourseIds = $user->coordinatedCourses()->pluck('id');

            return $coordinatedCourseIds->contains($internship->course_id)
                || $internship->advisor_id === $user->id;
        }

        return false;
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
    public function update(User $user, Internship $internship): bool
    {
        return $user->can('is-admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Internship $internship): bool
    {
        return $user->can('is-admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Internship $internship): bool
    {
        return $user->can('is-admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Internship $internship): bool
    {
        return $user->can('is-admin');
    }
}
