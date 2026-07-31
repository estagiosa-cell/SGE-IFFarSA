<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class UpdateInternshipTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->can('update', Route::current()->parameter('internship_type'));
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
            'course_id' => ['required', 'exists:courses,id'],
            'great_value' => ['required', 'numeric', 'min:0'],
            'very_good_value' => ['required', 'numeric', 'min:0'],
            'good_value' => ['required', 'numeric', 'min:0'],
            'satisfactory_value' => ['required', 'numeric', 'min:0'],
            'unsatisfactory_value' => ['required', 'numeric', 'min:0'],
        ];
    }
}
