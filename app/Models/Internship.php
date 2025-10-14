<?php

namespace App\Models;

use App\Enums\InternshipStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Internship extends Model
{
    use SoftDeletes;

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

        // Google Docs
        'google_docs_id',
    ];

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

    // Relacionamentos
    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    // Métodos auxiliares
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

    public function isCompanyCnpj(): bool
    {
        return $this->company_legal_identifier_type === 'CNPJ';
    }

    public function isCompanyCpf(): bool
    {
        return $this->company_legal_identifier_type === 'CPF';
    }

    public function needsLegalGuardian(): bool
    {
        return !$this->student_is_adult;
    }

    // Métodos de avaliação
    public function hasEvaluation(): bool
    {
        return !is_null($this->evaluation_submitted_at);
    }

    public function calculateEvaluationTotalScore(): float
    {
        if (!$this->hasEvaluation()) {
            return 0.0;
        }

        return (float)(
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

    public function getEvaluationPercentage(): float
    {
        if (!$this->hasEvaluation()) {
            return 0.0;
        }

        // Converte a nota de 0-20 para 0-100
        return ($this->evaluation_total_score / 20) * 100;
    }
}
