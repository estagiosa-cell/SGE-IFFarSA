<?php

namespace App\Models;

use App\Enums\InternshipStatus;
use App\Utils\SearchHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

/**
 * Model que representa um estágio.
 *
 * Gerencia todas as informações relacionadas a um estágio, incluindo dados do estudante,
 * empresa concedente, supervisor, orientador, carga horária, avaliações e status.
 */
class Internship extends Model
{
    use SoftDeletes;

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Chaves Estrangeiras
        'advisor_id',
        'course_id',

        // Dados do Aluno
        'student_name',
        'student_email',
        'student_registration_number',
        'student_year_semester',
        'student_birth_date',
        'student_is_adult',
        'student_rg',
        'student_rg_issuer',
        'student_rg_issue_date',
        'student_cpf',
        'student_phone',
        'student_address_street',
        'student_address_number',
        'student_address_neighborhood',
        'student_address_city',
        'student_address_state',
        'student_address_zip',

        // Dados da Concedente
        'company_legal_identifier',
        'company_name',
        'company_phone',
        'company_email',
        'company_address_street',
        'company_address_number',
        'company_address_neighborhood',
        'company_address_city',
        'company_address_state',
        'company_address_zip',
        'company_representative_name',
        'company_representative_role',
        'field_of_activity',
        'professional_council',
        'council_registration_number',
        'process_number',

        // Dados do Responsável Legal
        'legal_guardian_name',
        'legal_guardian_cpf',
        'legal_guardian_kinship',
        'legal_guardian_email',

        // Dados do Supervisor
        'supervisor_name',
        'supervisor_phone',
        'supervisor_email',
        'supervisor_role',
        'supervisor_qualification',
        'supervisor_training',
        'supervisor_experience',

        // Dados do Estágio
        'internship_type_name',
        'activities',
        'start_date',
        'end_date',
        'status',
        'notes',
        'internship_sector',
        'required_hours',
        'internship_type_weight',

        // Carga Horária
        'hours_sunday',
        'hours_monday',
        'hours_tuesday',
        'hours_wednesday',
        'hours_thursday',
        'hours_friday',
        'hours_saturday',

        // Remuneração
        'is_remunerated',
        'grant_value',
        'transportation_allowance',

        // Avaliação
        'evaluation_grade',
        'great_value',
        'very_good_value',
        'good_value',
        'satisfactory_value',
        'unsatisfactory_value',

        // Google Docs
        'google_docs_id',

        // Avaliação do Supervisor
        'evaluation_supervisor_email',
        'evaluation_supervisor_name',
        'evaluation_has_academic_background',
        'evaluation_completed_workload',
        'evaluation_training_course',
        'evaluation_education_level',
        'evaluation_job_role',
        'evaluation_experience_time',
        'evaluation_performance',
        'evaluation_comprehension',
        'evaluation_technical_knowledge',
        'evaluation_organization',
        'evaluation_initiative',
        'evaluation_attendance',
        'evaluation_discipline',
        'evaluation_sociability',
        'evaluation_cooperation',
        'evaluation_responsibility',
        'evaluation_considerations',
        'evaluation_suggestions_to_institution',
        'evaluation_performance_issues',
        'evaluation_other_observations',
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'student_birth_date' => 'date',
        'student_is_adult' => 'boolean',
        'student_rg_issue_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => InternshipStatus::class,
        'is_remunerated' => 'boolean',
        'grant_value' => 'decimal:2',
        'transportation_allowance' => 'decimal:2',
        'evaluation_grade' => 'decimal:2',
        'hours_sunday' => 'integer',
        'hours_monday' => 'integer',
        'hours_tuesday' => 'integer',
        'hours_wednesday' => 'integer',
        'hours_thursday' => 'integer',
        'hours_friday' => 'integer',
        'hours_saturday' => 'integer',
    ];

    /**
     * Relacionamento: retorna o orientador responsável pelo estágio.
     */
    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    /**
     * Relacionamento: retorna o curso ao qual o estágio pertence.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Aplica filtros padrão de listagem de estágios na query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array<string, mixed>  $options
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApplyStandardFilters($query, Request $request, array $options = [])
    {
        $allowAdvisorFilter = (bool) ($options['allow_advisor_filter'] ?? false);
        $allowCourseFilter = (bool) ($options['allow_course_filter'] ?? false);
        $skipNameSearch = (bool) ($options['skip_name_search'] ?? false);

        if (! $skipNameSearch && $request->filled('search')) {
            SearchHelper::searchInField($query, $request->search, 'student_name');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($allowAdvisorFilter && $request->filled('advisor')) {
            $query->where('advisor_id', $request->advisor);
        }

        if ($allowCourseFilter && $request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('registration')) {
            $query->where('student_registration_number', 'like', '%'.$request->registration.'%');
        }

        if ($request->filled('end_date_from')) {
            $query->whereDate('end_date', '>=', $request->end_date_from);
        }

        if ($request->filled('end_date_to')) {
            $query->whereDate('end_date', '<=', $request->end_date_to);
        }

        return $query;
    }

    /**
     * Calcula o total de horas semanais do estágio.
     *
     * @return int Total de horas semanais.
     */
    public function getTotalWeeklyHours(): int
    {
        return collect([
            $this->hours_sunday,
            $this->hours_monday,
            $this->hours_tuesday,
            $this->hours_wednesday,
            $this->hours_thursday,
            $this->hours_friday,
            $this->hours_saturday,
        ])->filter()->sum();
    }

    /**
     * Verifica se o identificador legal da empresa é um CNPJ.
     *
     * @return bool True se for CNPJ, false caso contrário.
     */
    public function isCompanyCnpj(): bool
    {
        return $this->company_legal_identifier_type === 'CNPJ';
    }

    /**
     * Verifica se o identificador legal da empresa é um CPF.
     *
     * @return bool True se for CPF, false caso contrário.
     */
    public function isCompanyCpf(): bool
    {
        return $this->company_legal_identifier_type === 'CPF';
    }

    /**
     * Verifica se o estudante precisa de responsável legal.
     *
     * @return bool True se o estudante for menor de idade, false caso contrário.
     */
    public function needsLegalGuardian(): bool
    {
        return ! $this->student_is_adult;
    }

    /**
     * Verifica se o estágio possui avaliação do supervisor.
     *
     * @return bool True se houver avaliação, false caso contrário.
     */
    public function hasEvaluation(): bool
    {
        return ! is_null($this->evaluation_submitted_at);
    }

    /**
     * Calcula a pontuação total da avaliação do supervisor.
     *
     * Soma os valores de todos os critérios de avaliação (10 questões).
     *
     * @return float Pontuação total da avaliação.
     */
    public function calculateEvaluationTotalScore(): float
    {
        if (! $this->hasEvaluation()) {
            return 0.0;
        }

        return (float) (
            ($this->evaluation_q1_performance ?? 0) +
            ($this->evaluation_q2_comprehension ?? 0) +
            ($this->evaluation_q3_technical_knowledge ?? 0) +
            ($this->evaluation_q4_organization ?? 0) +
            ($this->evaluation_q5_initiative ?? 0) +
            ($this->evaluation_q6_attendance ?? 0) +
            ($this->evaluation_q7_discipline ?? 0) +
            ($this->evaluation_q8_sociability ?? 0) +
            ($this->evaluation_q9_cooperation ?? 0) +
            ($this->evaluation_q10_responsibility ?? 0)
        );
    }

    /**
     * Calcula a nota da avaliação baseando-se nos valores guardados nos atributos e
     * eventuais dados atualizados (via form ou memória).
     *
     * @param  array  $data  Dados adicionais/sobrescritos (ex: do request)
     * @return float A nota final calculada.
     */
    public function calculateEvaluationGrade(array $data = []): float
    {
        $criteria = [
            'evaluation_performance',
            'evaluation_comprehension',
            'evaluation_technical_knowledge',
            'evaluation_organization',
            'evaluation_initiative',
            'evaluation_attendance',
            'evaluation_discipline',
            'evaluation_sociability',
            'evaluation_cooperation',
            'evaluation_responsibility',
        ];

        $totalScore = 0.0;
        $count = 0;

        foreach ($criteria as $criterion) {
            $text = $data[$criterion] ?? $this->{$criterion};
            if (! empty($text)) {
                $totalScore += $this->getEvaluationNumericValue($text, $data);
                $count++;
            }
        }

        return $count > 0 ? $totalScore / $count : 0.0;
    }

    /**
     * Retorna o valor de uma nota textual (Ótimo, Bom...) respeitando os pesos do estágio.
     */
    public function getEvaluationNumericValue(?string $value, array $data = []): float
    {
        $defaults = [
            'Ótimo' => 2.0,
            'Muito Bom' => 1.5,
            'Bom' => 1.0,
            'Satisfatório' => 0.5,
            'Insatisfatório' => 0.0,
        ];

        if (empty($value)) {
            return 0.0;
        }

        $map = [
            'Ótimo' => 'great_value',
            'Muito Bom' => 'very_good_value',
            'Bom' => 'good_value',
            'Satisfatório' => 'satisfactory_value',
            'Insatisfatório' => 'unsatisfactory_value',
        ];

        $key = $map[$value] ?? null;

        if ($key && isset($data[$key]) && is_numeric($data[$key])) {
            return (float) $data[$key];
        }

        if ($key && isset($this->{$key})) {
            return (float) $this->{$key};
        }

        return $defaults[$value] ?? 0.0;
    }
}
