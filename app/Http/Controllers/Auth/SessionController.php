<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\LoginRedirectService;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador responsável por gerenciar a sessão do usuário (login e logout).
 */
class SessionController extends Controller
{
    /**
     * Exibe o formulário de login.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Autentica o usuário e cria uma nova sessão.
     *
     * @param  \App\Http\Requests\LoginRequest  $request  Os dados da requisição de login.
     * @param  \App\Services\LoginRedirectService  $redirector  O serviço para redirecionar o usuário após o login.
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException Se a autenticação falhar ou o usuário estiver inativo.
     */
    public function store(LoginRequest $request, LoginRedirectService $redirector)
    {
        $request->authenticate();

        // Se a autenticação for bem-sucedida, regenera a sessão para evitar session fixation.
        session()->regenerate();

        // Pede ao serviço para determinar a rota correta com base no perfil do usuário.
        $redirectRouteName = $redirector->getRedirectRoute(Auth::user());

        // Redireciona o usuário para a rota pretendida ou para o dashboard padrão do seu perfil.
        return redirect()->intended(route($redirectRouteName))
            ->with('message', 'Usuário autenticado com sucesso!')
            ->with('messageType', 'success');
    }

    /**
     * Realiza o logout do usuário, encerrando a sessão.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy()
    {
        Auth::logout();

        // Invalida a sessão atual e gera um novo token CSRF.
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login')
            ->with('message', 'Você foi deslogado com sucesso!')
            ->with('messageType', 'success');
    }
}
