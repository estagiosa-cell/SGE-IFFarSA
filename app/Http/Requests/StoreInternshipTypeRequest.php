<?php

namespace App\Http\Requests;

use App\Models\InternshipType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class StoreInternshipTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->can('create', InternshipType::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'required_hours' => ['required', 'integer', 'min:1'],
            'weight' => ['required', 'integer', 'min:1', 'max:10'],
            'report_weight' => ['required', 'integer', 'min:1', 'max:10'],
            'presentation_weight' => ['required', 'integer', 'min:1', 'max:10'],
            'course_id' => ['required', 'exists:courses,id'],
            'great_value' => ['required', 'numeric', 'min:0'],
            'very_good_value' => ['required', 'numeric', 'min:0'],
            'good_value' => ['required', 'numeric', 'min:0'],
            'satisfactory_value' => ['required', 'numeric', 'min:0'],
            'unsatisfactory_value' => ['required', 'numeric', 'min:0'],
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
            if (array_sum([
                (int) $this->input('weight'),
                (int) $this->input('report_weight'),
                (int) $this->input('presentation_weight'),
            ]) !== 10) {
                $validator->errors()->add('weight', 'A soma dos pesos da concedente, do relatório e da apresentação deve ser 10.');
            }
        }];
    }
}
