<?php


namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * @method mixed input(string $key = null, $default = null)
 * @method mixed route($param = null, $default = null)
 */
class InternshipTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('is-admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'required_hours' => ['required', 'integer', 'min:1'],
            'weight' => ['required', 'integer', 'min:1', 'max:10'],
            'course_id' => ['required', 'exists:courses,id'],
        ];
    }
}
