<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\Internship;
use App\Models\User;

class InternshipPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admins, orientadores, coordenadores e direção de ensino podem ver a lista de estágios
        return $user->can('is-admin')
            || $user->can('is-orientador')
            || $user->can('is-coordenador')
            || $user->can('is-direcao-ensino');
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

        // Direção de ensino pode ver qualquer estágio
        if ($user->can('is-direcao-ensino')) {
            return true;
        }

        // Orientadores podem ver estágios que eles orientam
        if ($user->can('is-orientador') && $internship->advisor_id === $user->id) {
            return true;
        }

        // Coordenadores podem ver estágios dos cursos que coordenam ou que eles próprios orientam
        if ($user->can('is-coordenador')) {
            $canViewByCourse = Course::where('id', $internship->course_id)
                ->where(function ($query) use ($user) {
                    $query->where('coordinator_id', $user->id)
                        ->orWhere('secondary_coordinator_id', $user->id);
                })
                ->exists();

            return $canViewByCourse || $internship->advisor_id === $user->id;
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
     * Determine whether the advisor can register report and presentation grades.
     */
    public function updateAdvisorGrades(User $user, Internship $internship): bool
    {
        return ($user->can('is-orientador') || $user->can('is-coordenador'))
            && $internship->advisor_id === $user->id;
    }

    /**
     * Determine whether the user can generate the course orientation certificate.
     */
    public function generateOrientationCertificate(User $user, Internship $internship): bool
    {
        if (! $user->can('is-coordenador')) {
            return false;
        }

        $course = $internship->relationLoaded('course')
            ? $internship->course
            : $internship->course()->first(['id', 'coordinator_id', 'secondary_coordinator_id']);

        return $course !== null
            && ((string) $course->coordinator_id === (string) $user->id
                || (string) $course->secondary_coordinator_id === (string) $user->id);
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
