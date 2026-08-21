<?php

namespace App\Http\Requests;

use App\Models\Internship;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateAdvisorGradesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $internship = $this->route('internship');

        return $internship instanceof Internship
            && $this->user()?->can('updateAdvisorGrades', $internship);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Internship $internship */
        $internship = $this->route('internship');

        $reportGradeRules = ['nullable', 'numeric', 'decimal:0,1', 'min:0'];
        $presentationGradeRules = ['nullable', 'numeric', 'decimal:0,1', 'min:0'];

        if ($internship->report_weight !== null) {
            $reportGradeRules[] = 'max:'.$internship->report_weight;
        }

        if ($internship->presentation_weight !== null) {
            $presentationGradeRules[] = 'max:'.$internship->presentation_weight;
        }

        return [
            'report_grade' => $reportGradeRules,
            'presentation_grade' => $presentationGradeRules,
        ];
    }

    /**
     * Configure the validator after its initial rules run.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            /** @var Internship $internship */
            $internship = $this->route('internship');

            if (! $internship->hasGradeWeightsConfigured()) {
                $validator->errors()->add('report_grade', 'Os pesos das avaliações devem ser configurados pelo administrador antes do lançamento das notas.');
            }
        }];
    }
}
