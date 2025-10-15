<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupervisorEvaluation extends Model
{
    use SoftDeletes;

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
     * Verifica se o estagiário cumpriu a carga horária
     */
    public function hasCompletedWorkload(): bool
    {
        return strtolower($this->completed_workload ?? '') === 'sim';
    }

    /**
     * Converte a resposta textual para valor numérico
     */
    private function getNumericValue(?string $value): float
    {
        return match ($value) {
            'Ótimo' => 2.0,
            'Muito Bom' => 1.5,
            'Bom' => 1.0,
            'Satisfatório' => 0.5,
            'Insatisfatório' => 0.0,
            default => 0.0,
        };
    }

    /**
     * Calcula a nota total da avaliação (soma dos 10 critérios)
     * Máximo: 20.0 pontos (10 critérios x 2.0)
     */
    public function calculateGrade(): float
    {
        return
            $this->getNumericValue($this->performance) +
            $this->getNumericValue($this->comprehension) +
            $this->getNumericValue($this->technical_knowledge) +
            $this->getNumericValue($this->organization) +
            $this->getNumericValue($this->initiative) +
            $this->getNumericValue($this->attendance) +
            $this->getNumericValue($this->discipline) +
            $this->getNumericValue($this->sociability) +
            $this->getNumericValue($this->cooperation) +
            $this->getNumericValue($this->responsibility);
    }
}
