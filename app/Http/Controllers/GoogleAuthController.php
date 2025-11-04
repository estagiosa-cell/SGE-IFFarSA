<?php

namespace App\Http\Controllers;

use Google\Service\Oauth2 as GoogleServiceOauth2;
use Google_Client;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

/**
 * Controlador para gerenciar a autenticação com a API do Google (OAuth2).
 *
 * Lida com o redirecionamento para a tela de consentimento do Google e
 * o processamento do callback após a autorização do usuário.
 */
class GoogleAuthController extends Controller
{
    use AuthorizesRequests;

    /**
     * Redireciona o usuário para o fluxo de autenticação do Google.
     *
     * Configura o cliente da API do Google com as credenciais, escopos
     * e outras configurações necessárias para a autorização.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        // Verifica se o usuário tem permissão de admin antes de iniciar o OAuth
        if (! Auth::check() || ! Auth::user()->can('is-admin')) {
            abort(403, 'Você não tem permissão para conectar uma conta Google.');
        }

        $client = new Google_Client;

        // Configurações do cliente a partir das variáveis de ambiente.
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));

        // Define os escopos de permissão necessários para a aplicação.
        $client->setScopes([
            'https://www.googleapis.com/auth/drive',
            'https://www.googleapis.com/auth/spreadsheets',
            'https://www.googleapis.com/auth/documents',
            'https://www.googleapis.com/auth/userinfo.email',
        ]);

        // 'offline' permite que a aplicação obtenha um refresh token.
        $client->setAccessType('offline');
        // 'consent' força a exibição da tela de consentimento do Google.
        $client->setPrompt('consent');

        // Redireciona o usuário para a URL de autorização gerada.
        return Redirect::to($client->createAuthUrl());
    }

    /**
     * Processa o callback de autenticação do Google após o usuário conceder permissão.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request)
    {
        // Se o 'code' não estiver presente, o usuário cancelou a autorização.
        if (! $request->has('code')) {
            Auth::logout();

            return redirect()->route('login')
                ->with('message', 'Autorização do Google foi cancelada.')
                ->with('messageType', 'warning');
        }

        // Configura o cliente da API novamente para trocar o código pelo token de acesso.
        $client = new Google_Client;
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));

        // Troca o código de autorização por um token de acesso.
        $token = $client->fetchAccessTokenWithAuthCode($request->input('code'));

        // Se houver um erro na obtenção do token, redireciona com erro.
        if (isset($token['error'])) {
            Auth::logout();

            return redirect()->route('login')
                ->with('message', 'Erro ao conectar com o Google. Tente novamente.')
                ->with('messageType', 'danger');
        }

        $client->setAccessToken($token);

        try {
            // Obtém as informações do usuário a partir do serviço Oauth2.
            $oauth2Service = new GoogleServiceOauth2($client);
            $userInfo = $oauth2Service->userinfo->get();
            $googleUserEmail = $userInfo->getEmail();

            // Verifica se o e-mail do administrador está configurado no sistema.
            $allowedEmail = config('services.google.admin_email');
            if (! $allowedEmail) {
                throw new \Exception('E-mail de administrador não configurado');
            }

            // Compara o e-mail do usuário logado no Google com o e-mail permitido.
            if ($googleUserEmail !== $allowedEmail) {
                // Se não for o e-mail correto, revoga o token e desloga o usuário.
                $client->revokeToken($token['access_token']);
                Auth::logout();

                session()->invalidate();
                session()->regenerateToken();

                return redirect()->route('login')
                    ->with('message', 'Conta Google não autorizada.')
                    ->with('messageType', 'danger');
            }
        } catch (\Exception $e) {
            // Em caso de qualquer exceção, revoga o token e desloga.
            $client->revokeToken($token['access_token']);
            Auth::logout();

            return redirect()->route('login')
                ->with('message', 'Erro ao verificar dados da conta Google. Tente novamente.')
                ->with('messageType', 'danger');
        }

        // Armazena o token do Google na sessão do usuário.
        $request->session()->put('google_oauth_token', $token);

        return redirect()->route('admin.dashboard')
            ->with('message', 'Conta Google conectada com sucesso!')
            ->with('messageType', 'success');
    }
}
