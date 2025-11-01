<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Qualquer pessoa pode tentar fazer login, então retornamos true.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
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
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        if (! Auth::attempt($this->validated())) {
            throw ValidationException::withMessages([
                'login_error' => __('auth.failed'),
            ]);
        }

        if (! Auth::user()->isActive()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'login_error' => 'Esta conta de usuário foi desativada.',
            ]);
        }
    }
}
