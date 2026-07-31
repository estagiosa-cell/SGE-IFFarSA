<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateSupervisorEvaluationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->can('is-admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'supervisor_email' => 'nullable|email|max:255',
            'student_name' => 'nullable|string|max:255',
            'supervisor_name' => 'nullable|string|max:255',
            'has_academic_background' => 'nullable|string|max:255',
            'completed_workload' => 'nullable|string|max:255',
            'training_course' => 'nullable|string|max:255',
            'education_level' => 'nullable|string|max:255',
            'job_role' => 'nullable|string|max:255',
            'experience_time' => 'nullable|string|max:255',
            'performance' => 'nullable|string|max:255',
            'comprehension' => 'nullable|string|max:255',
            'technical_knowledge' => 'nullable|string|max:255',
            'organization' => 'nullable|string|max:255',
            'initiative' => 'nullable|string|max:255',
            'attendance' => 'nullable|string|max:255',
            'discipline' => 'nullable|string|max:255',
            'sociability' => 'nullable|string|max:255',
            'cooperation' => 'nullable|string|max:255',
            'responsibility' => 'nullable|string|max:255',
            'considerations' => 'nullable|string',
            'suggestions_to_institution' => 'nullable|string',
            'performance_issues' => 'nullable|string',
            'other_observations' => 'nullable|string',
        ];
    }
}
