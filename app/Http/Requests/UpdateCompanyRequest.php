<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Apenas administradores podem editar as partes concedentes.
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
            // Identificação (Obrigatórios)
            'name' => ['required', 'string', 'max:255'],
            'legal_identifier' => ['required', 'string', 'regex:/^[0-9]+$/', 'min:11', 'max:14'], // Apenas números, 11 (CPF) ou 14 (CNPJ)

            // Endereço (Obrigatórios)
            'address_street' => ['required', 'string', 'max:255'],
            'address_number' => ['required', 'string', 'max:50'],
            'address_neighborhood' => ['required', 'string', 'max:255'],
            'address_city' => ['required', 'string', 'max:255'],
            'address_state' => ['required', Rule::in([
                'Acre',
                'Alagoas',
                'Amapá',
                'Amazonas',
                'Bahia',
                'Ceará',
                'Distrito Federal',
                'Espírito Santo',
                'Goiás',
                'Maranhão',
                'Mato Grosso',
                'Mato Grosso do Sul',
                'Minas Gerais',
                'Pará',
                'Paraíba',
                'Paraná',
                'Pernambuco',
                'Piauí',
                'Rio de Janeiro',
                'Rio Grande do Norte',
                'Rio Grande do Sul',
                'Rondônia',
                'Roraima',
                'Santa Catarina',
                'São Paulo',
                'Sergipe',
                'Tocantins'
            ])],
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
