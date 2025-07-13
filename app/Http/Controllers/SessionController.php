<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Gate;

class SessionController extends Controller
{
    /**
     * Exibe o formulário de login
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Processa o login do usuário
     */
    public function store(LoginRequest $request)
    {

        // Tenta autenticar o usuário com as credenciais fornecidas
        if (! Auth::attempt($request->validated())) {
            // Se falhar, lança uma exceção de validação com mensagem de erro
            throw ValidationException::withMessages([
                'login_error' => __('auth.failed'),
            ]);
        }

        // Se a autenticação for bem-sucedida
        session()->regenerate();

        // Redireciona o usuário para a rota pretendida ou para o dashboard
        return redirect()->intended(route('dashboard'))
            ->with('message', 'Usuário autenticado com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Realiza o logout do usuário
     */
    public function destroy()
    {
        Auth::logout();

        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login')
            ->with('message', 'Você foi deslogado com sucesso!')
            ->with('messageType', 'success');
    }
}
