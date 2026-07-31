<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Request para validação de dados ao criar um novo usuário.
 *
 * Valida os campos obrigatórios e garante que o e-mail seja único no sistema.
 */
class StoreUserRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     *
     * Verifica se o usuário autenticado tem permissão para criar novos usuários.
     *
     * @return bool True se autorizado, false caso contrário.
     */
    public function authorize(): bool
    {
        return Auth::user()->can('create', User::class);
    }

    /**
     * Define as regras de validação que se aplicam à requisição.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users'),
                'confirmed',
            ],
            'role' => [
                'required',
                'string',
                Rule::enum(UserRole::class),
            ],
        ];
    }
}
