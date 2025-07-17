<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\LoginRequest;
use App\Services\LoginRedirectService;

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
    public function store(LoginRequest $request, LoginRedirectService $redirector)
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

        // 3. Pede ao serviço para determinar a rota correta, passando o utilizador logado
        $redirectRouteName = $redirector->getRedirectRoute(Auth::user());

        // Redireciona o usuário para a rota pretendida ou para o dashboard
        return redirect()->intended(route($redirectRouteName))
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
