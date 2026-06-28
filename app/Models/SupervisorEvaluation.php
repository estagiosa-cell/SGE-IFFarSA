<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Searchable;

/**
 * Model que representa uma avaliação feita pelo supervisor de estágio.
 *
 * Armazena as respostas do formulário de avaliação preenchido pelo
 * supervisor da empresa sobre o desempenho do estagiário.
 */
class SupervisorEvaluation extends Model
{
    use Searchable;
    use SoftDeletes;

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'supervisor_email',
        'student_name',
        'supervisor_name',
        'has_academic_background',
        'completed_workload',
        'training_course',
        'education_level',
        'job_role',
        'experience_time',
        'performance',
        'comprehension',
        'technical_knowledge',
        'organization',
        'initiative',
        'attendance',
        'discipline',
        'sociability',
        'cooperation',
        'responsibility',
        'considerations',
        'suggestions_to_institution',
        'performance_issues',
        'other_observations',
    ];

    /**
     * Verifica se o estagiário cumpriu a carga horária completa.
     *
     * @return bool True se cumpriu, false caso contrário.
     */
    public function hasCompletedWorkload(): bool
    {
        return strtolower($this->completed_workload ?? '') === 'sim';
    }
}
