<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class CnpjExceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('is-admin');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cnpj_matriz' => preg_replace('/[^0-9]/', '', $this->input('cnpj_matriz')),
        ]);
    }

    public function rules(): array
    {
        $exceptionId = $this->route('cnpj_exception') ? $this->route('cnpj_exception')->id : null;

        return [
            'cnpj_matriz' => [
                'bail',
                'required',
                'string',
                'regex:/^\d{14}$/',
                Rule::unique('cnpj_exceptions', 'cnpj_matriz')->ignore($exceptionId),
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Se já houver erros de validação para o CNPJ, não prossegue.
            if ($validator->errors()->has('cnpj_matriz')) {
                return;
            }

            $cnpj = $this->input('cnpj_matriz');

            if ($cnpj) {
                $response = Http::timeout(10)->get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");

                // 1. Verifica se o CNPJ é válido na API
                if (!$response->ok()) {
                    $validator->errors()->add(
                        'cnpj_matriz',
                        'O CNPJ informado não é válido ou não foi encontrado na BrasilAPI.'
                    );
                    return; // Para a execução se o CNPJ é inválido
                }

                // 2. Verifica se o CNPJ é da Matriz
                $data = $response->json();
                if (isset($data['descricao_identificador_matriz_filial']) && $data['descricao_identificador_matriz_filial'] !== 'MATRIZ') {
                    $validator->errors()->add(
                        'cnpj_matriz',
                        'O CNPJ informado pertence a uma filial. Por favor, insira o CNPJ da matriz.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'cnpj_matriz.required' => 'O campo CNPJ é obrigatório.',
            'cnpj_matriz.regex' => 'O formato do CNPJ é inválido. Deve conter 14 dígitos.',
            'cnpj_matriz.unique' => 'Este CNPJ já está cadastrado como uma exceção.',
        ];
    }
}
