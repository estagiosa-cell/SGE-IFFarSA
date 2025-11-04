<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model que representa uma avaliação feita pelo supervisor de estágio.
 *
 * Armazena as respostas do formulário de avaliação preenchido pelo
 * supervisor da empresa sobre o desempenho do estagiário.
 */
class SupervisorEvaluation extends Model
{
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

    /**
     * Converte a resposta textual de um critério para seu valor numérico.
     *
     * Mapeia as respostas qualitativas em valores quantitativos para cálculo da nota final.
     *
     * @param string|null $value A resposta textual do critério.
     * @return float O valor numérico correspondente.
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
     * Calcula a nota total da avaliação somando todos os critérios.
     *
     * A nota máxima é 20.0 pontos (10 critérios × 2.0 pontos cada).
     *
     * @return float A nota total da avaliação.
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
