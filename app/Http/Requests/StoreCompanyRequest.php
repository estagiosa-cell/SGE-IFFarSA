<?php

namespace App\Http\Requests;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->can('create', Company::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Identificação (Obrigatórios)
            'name' => ['required', 'string', 'max:255'],
            'legal_identifier' => ['required', 'string', 'regex:/^[0-9]+$/', 'min:11', 'max:14'], // Apenas números, 11 (CPF) ou 14 (CNPJ)

            // Endereço (Obrigatórios)
            'address_street' => ['required', 'string', 'max:255'],
            'address_number' => ['required', 'string', 'max:50'],
            'address_neighborhood' => ['required', 'string', 'max:255'],
            'address_city' => ['required', 'string', 'max:255'],
            'address_state' => ['required', Rule::in(['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'])],
            'address_zip' => ['required', 'string', 'regex:/^[0-9]{8}$/'], // Apenas 8 números

            // Representante (Obrigatórios)
            'representative_name' => ['required', 'string', 'max:255'],
            'representative_role' => ['required', 'string', 'max:255'],

            // Contato (Opcionais)
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],

            // Informações Adicionais
            'field_of_activity' => ['required', 'string', 'max:255'], // Obrigatório
            'professional_council' => ['nullable', 'string', 'max:255'],
            'council_registration_number' => ['nullable', 'string', 'max:255'],
            'process_number' => ['nullable', 'string', 'max:255'],
        ];
    }
}
