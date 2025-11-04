<?php

namespace App\Http\Requests;

use App\Models\Internship;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GenerateInternshipDocumentRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'document_type' => [
                'required',
                'string',
                Rule::in([
                    'termo-compromisso',
                    'termo-emater-rs',
                    'termo-seduc',
                    'rescisao',
                    'credenciamento',
                ]),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'document_type.required' => 'O tipo de documento é obrigatório.',
            'document_type.in' => 'O tipo de documento selecionado é inválido.',
        ];
    }
}
