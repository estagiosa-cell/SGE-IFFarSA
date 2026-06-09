<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Request para validação de dados de login.
 *
 * Valida as credenciais fornecidas e fornece método para autenticação.
 */
class LoginRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     *
     * @return bool True, pois qualquer pessoa pode tentar fazer login.
     */
    public function authorize(): bool
    {
        // Qualquer pessoa pode tentar fazer login, então retornamos true.
        return true;
    }

    /**
     * Define as regras de validação que se aplicam à requisição.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }

    /**
     * Tenta autenticar as credenciais da requisição.
     *
     * Verifica se as credenciais são válidas e se o usuário está ativo no sistema.
     *
     * @throws \Illuminate\Validation\ValidationException Se as credenciais forem inválidas ou o usuário estiver desativado.
     */
    public function authenticate(): void
    {
        // Tenta autenticar com as credenciais fornecidas.
        if (! Auth::attempt($this->validated())) {
            throw ValidationException::withMessages([
                'login_error' => __('auth.failed'),
            ]);
        }

        // Verifica se o usuário está ativo no sistema.
        if (! Auth::user()->isActive()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'login_error' => 'Esta conta de usuário foi desativada.',
            ]);
        }
    }
}
