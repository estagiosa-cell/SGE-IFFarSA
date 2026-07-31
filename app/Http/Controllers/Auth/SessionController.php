<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\LoginRedirectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Controlador responsável por gerenciar a sessão do usuário (login e logout).
 */
class SessionController extends Controller
{
    /**
     * Exibe o formulário de login.
     *
     * @return View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Autentica o usuário e cria uma nova sessão.
     *
     * @param  LoginRequest  $request  Os dados da requisição de login.
     * @param  LoginRedirectService  $redirector  O serviço para redirecionar o usuário após o login.
     * @return RedirectResponse
     *
     * @throws ValidationException Se a autenticação falhar ou o usuário estiver inativo.
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
     * @return RedirectResponse
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
