<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class UpdateCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->can('update', Route::current()->parameter('course'));
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
            'coordinator_id' => ['nullable', 'exists:users,id'],
            'secondary_coordinator_id' => ['nullable', 'exists:users,id', 'different:coordinator_id'],
        ];
    }

    /**
     * Mensagens de erro personalizadas
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome do curso é obrigatório.',
            'coordinator_id.exists' => 'O coordenador selecionado não existe.',
            'secondary_coordinator_id.exists' => 'O coordenador secundário selecionado não existe.',
            'secondary_coordinator_id.different' => 'O coordenador secundário não pode ser o mesmo do coordenador principal.',
        ];
    }
}
