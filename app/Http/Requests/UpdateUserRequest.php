<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $userToUpdate = Route::current()->parameter('user');

        return Auth::user()->can('update', $userToUpdate);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore(Route::current()->parameter('user')),
            ],
            'role' => [
                'required',
                'string',
                Rule::enum(UserRole::class),
            ],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $request = $this;

        $validator->after(function ($validator) use ($request) {
            $userToUpdate = Route::current()->parameter('user');
            $currentUser = Auth::user();
            $newRole = $request->input('role');

            // Verifica se o usuário está tentando alterar o próprio papel
            if ($currentUser->id === $userToUpdate->id && $currentUser->role->value !== $newRole) {
                $validator->errors()->add('role', 'Não é possível alterar o próprio papel.');
            }

            // Verifica se está tentando alterar o papel de um coordenador com cursos atrelados
            if ($userToUpdate->role === UserRole::COORDENADOR
                && $userToUpdate->coordinatedCourses()->exists()
                && $newRole !== UserRole::COORDENADOR->value) {
                $validator->errors()->add('role', 'Não é possível alterar o papel de um coordenador com cursos atrelados.');
            }
        });
    }
}
