<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRules;

/**
 * Controlador que gerencia a lógica de redefinição de senha.
 *
 * Este controlador lida com o envio de links de redefinição de senha
 * e a atualização da senha do usuário.
 */
class PasswordResetController extends Controller
{
    /**
     * Exibe o formulário para solicitar o link de redefinição de senha.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.passwords.email');
    }

    /**
     * Processa a solicitação de envio do link de redefinição de senha.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Tenta enviar o link de redefinição de senha para o e-mail fornecido.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Verifica o status do envio e redireciona com a mensagem apropriada.
        return $status === Password::ResetLinkSent
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Exibe o formulário para redefinir a senha.
     *
     * @param  string  $token  O token de redefinição de senha.
     * @return \Illuminate\View\View
     */
    public function edit($token)
    {
        return view('auth.passwords.reset', ['token' => $token]);
    }

    /**
     * Processa a redefinição da senha do usuário.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRules::min(8)->max(64)->mixedCase()->numbers()->symbols()->uncompromised()],
        ]);

        // Tenta redefinir a senha usando o broker de senha do Laravel.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                // Atualiza a senha do usuário no banco de dados.
                $user->forceFill([
                    'password' => Hash::make($password),
                ]);

                $user->save();

                // Dispara o evento de redefinição de senha.
                event(new PasswordReset($user));
            }
        );

        // Verifica o status da redefinição e redireciona o usuário.
        return $status === Password::PasswordReset
            ? redirect()->route('login')->with('message', __($status))->with('messageType', 'success')
            : back()->withErrors(['email' => [__($status)]]);
    }
}
