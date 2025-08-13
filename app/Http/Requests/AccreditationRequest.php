<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AccreditationRequest extends FormRequest
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
        $accreditationId = request()->route('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'cpf' => [
                'required',
                'string',
                'max:14',
                Rule::unique('accreditations')->ignore($accreditationId)
            ],
            'process_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('accreditations')->ignore($accreditationId)
            ],
        ];
    }

    /**
     * Mensagens de erro personalizadas
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve ser um texto.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.string' => 'O CPF deve ser um texto.',
            'cpf.max' => 'O CPF não pode ter mais de 14 caracteres.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'process_number.required' => 'O número do processo é obrigatório.',
            'process_number.string' => 'O número do processo deve ser um texto.',
            'process_number.max' => 'O número do processo não pode ter mais de 255 caracteres.',
            'process_number.unique' => 'Este número de processo já está cadastrado.',
        ];
    }
}
