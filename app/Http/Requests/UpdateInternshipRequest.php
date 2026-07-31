<?php

namespace App\Http\Requests;

use App\Enums\InternshipStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class UpdateInternshipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->can('update', Route::current()->parameter('internship'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Informações do Sistema
            'advisor_id' => 'required|exists:users,id',
            'status' => 'required|in:'.implode(',', array_keys(InternshipStatus::options())),
            'notes' => 'nullable|string',

            // Dados do Aluno
            'student_name' => 'required|string|max:255',
            'student_email' => 'required|email|max:255',
            'student_registration_number' => 'required|string|max:50',
            'student_year_semester' => 'required|string|max:20',
            'student_birth_date' => 'required|date',
            'student_is_adult' => 'nullable|boolean',
            'student_rg' => 'required|string|max:20',
            'student_rg_issuer' => 'required|string|max:50',
            'student_rg_issue_date' => 'required|date',
            'student_cpf' => 'required|string|max:14',
            'student_phone' => 'required|string|max:20',

            // Endereço do Aluno
            'student_address_street' => 'required|string|max:255',
            'student_address_number' => 'required|string|max:20',
            'student_address_neighborhood' => 'required|string|max:100',
            'student_address_city' => 'required|string|max:100',
            'student_address_state' => 'required|string|size:2',
            'student_address_zip' => 'required|string|max:10',

            // Dados do Responsável Legal (obrigatório se o aluno for menor de idade)
            'legal_guardian_name' => 'nullable|required_if:student_is_adult,0|string|max:255',
            'legal_guardian_cpf' => 'nullable|required_if:student_is_adult,0|string|max:14',
            'legal_guardian_kinship' => 'nullable|required_if:student_is_adult,0|string|max:50',
            'legal_guardian_email' => 'nullable|required_if:student_is_adult,0|email|max:255',

            // Dados da Empresa/Parte Concedente
            'company_legal_identifier' => 'required|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'company_phone' => 'nullable|string|max:20',
            'company_email' => 'nullable|email|max:255',
            'company_representative_name' => 'nullable|string|max:255',
            'company_representative_role' => 'nullable|string|max:100',
            'field_of_activity' => 'nullable|string|max:255',

            // Endereço da Empresa
            'company_address_street' => 'nullable|string|max:255',
            'company_address_number' => 'nullable|string|max:20',
            'company_address_neighborhood' => 'nullable|string|max:100',
            'company_address_city' => 'nullable|string|max:100',
            'company_address_state' => 'nullable|string|size:2',
            'company_address_zip' => 'nullable|string|max:10',

            // Informações Adicionais da Empresa
            'professional_council' => 'nullable|string|max:100',
            'council_registration_number' => 'nullable|string|max:50',
            'process_number' => 'nullable|string|max:100',

            // Dados do Supervisor
            'supervisor_name' => 'required|string|max:255',
            'supervisor_phone' => 'nullable|string|max:20',
            'supervisor_email' => 'nullable|email|max:255',
            'supervisor_role' => 'required|string|max:100',

            // Dados do Estágio
            'internship_type_name' => 'required|string|max:100',
            'internship_sector' => 'nullable|string|max:100',
            'internship_type_weight' => 'nullable|integer|min:1|max:10',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'required_hours' => 'required|integer|min:1',
            'activities' => 'required|string',

            // Carga Horária Semanal
            'has_workload_exception' => 'boolean',
            'hours_sunday' => [
                'nullable',
                'integer',
                'min:0',
                Rule::when(! $this->boolean('has_workload_exception'), 'max:6'),
            ],
            'hours_monday' => [
                'nullable',
                'integer',
                'min:0',
                Rule::when(! $this->boolean('has_workload_exception'), 'max:6'),
            ],
            'hours_tuesday' => [
                'nullable',
                'integer',
                'min:0',
                Rule::when(! $this->boolean('has_workload_exception'), 'max:6'),
            ],
            'hours_wednesday' => [
                'nullable',
                'integer',
                'min:0',
                Rule::when(! $this->boolean('has_workload_exception'), 'max:6'),
            ],
            'hours_thursday' => [
                'nullable',
                'integer',
                'min:0',
                Rule::when(! $this->boolean('has_workload_exception'), 'max:6'),
            ],
            'hours_friday' => [
                'nullable',
                'integer',
                'min:0',
                Rule::when(! $this->boolean('has_workload_exception'), 'max:6'),
            ],
            'hours_saturday' => [
                'nullable',
                'integer',
                'min:0',
                Rule::when(! $this->boolean('has_workload_exception'), 'max:6'),
            ],

            // Remuneração
            'is_remunerated' => 'nullable|boolean',
            'grant_value' => 'nullable|numeric|min:0',
            'transportation_allowance' => 'nullable|numeric|min:0',

            // Avaliação do Supervisor
            'evaluation_supervisor_name' => 'nullable|string|max:255',
            'evaluation_supervisor_email' => 'nullable|email|max:255',
            'evaluation_has_academic_background' => 'nullable|string|max:255',
            'evaluation_completed_workload' => 'nullable|string|max:255',
            'evaluation_training_course' => 'nullable|string|max:255',
            'evaluation_education_level' => 'nullable|string|max:255',
            'evaluation_job_role' => 'nullable|string|max:255',
            'evaluation_experience_time' => 'nullable|string|max:255',
            'evaluation_performance' => 'nullable|string|max:255',
            'evaluation_comprehension' => 'nullable|string|max:255',
            'evaluation_technical_knowledge' => 'nullable|string|max:255',
            'evaluation_organization' => 'nullable|string|max:255',
            'evaluation_initiative' => 'nullable|string|max:255',
            'evaluation_attendance' => 'nullable|string|max:255',
            'evaluation_discipline' => 'nullable|string|max:255',
            'evaluation_sociability' => 'nullable|string|max:255',
            'evaluation_cooperation' => 'nullable|string|max:255',
            'evaluation_responsibility' => 'nullable|string|max:255',
            'evaluation_considerations' => 'nullable|string',
            'evaluation_suggestions_to_institution' => 'nullable|string',
            'evaluation_performance_issues' => 'nullable|string',
            'evaluation_other_observations' => 'nullable|string',
            'evaluation_grade' => 'nullable|numeric|min:0|max:20',

            // Valores customizáveis para conceitos (numéricos)
            'great_value' => 'required|numeric|min:0',
            'very_good_value' => 'required|numeric|min:0',
            'good_value' => 'required|numeric|min:0',
            'satisfactory_value' => 'required|numeric|min:0',
            'unsatisfactory_value' => 'required|numeric|min:0',
        ];
    }
}
