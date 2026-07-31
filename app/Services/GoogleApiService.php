<?php

namespace App\Services;

use Google_Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Serviço para gerenciar a integração com as APIs do Google.
 *
 * Responsável por inicializar o cliente da API do Google, gerenciar tokens de acesso
 * (incluindo renovação automática) e fornecer o cliente configurado para uso em outras partes do sistema.
 */
class GoogleApiService
{
    /**
     * Instância do cliente da API do Google.
     */
    public Google_Client $client;

    /**
     * Inicializa o cliente Google com configuração e token da sessão.
     *
     * Configura as credenciais do cliente usando as variáveis de ambiente,
     * verifica se existe um token armazenado na sessão e o aplica ao cliente.
     * Se o token estiver expirado, renova automaticamente.
     */
    public function __construct()
    {
        $this->client = new Google_Client;
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect'));

        // Se houver um token armazenado na sessão, aplica ao cliente.
        if (Session::has('google_oauth_token')) {
            $token = Session::get('google_oauth_token');
            $this->client->setAccessToken($token);

            // Verifica se o token de acesso expirou e renova se necessário.
            if ($this->client->isAccessTokenExpired()) {
                $this->refreshToken();
            }
        }
    }

    /**
     * Renova o token de acesso usando o refresh_token.
     *
     * Tenta obter um novo access_token usando o refresh_token armazenado.
     * Se a renovação falhar, chama o método de tratamento de falha.
     */
    protected function refreshToken(): void
    {
        $refreshToken = $this->client->getRefreshToken();

        if ($refreshToken) {
            // Tenta obter um novo access_token com o refresh_token.
            $newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);

            if (! isset($newToken['error'])) {
                // Preserva o refresh_token no novo token, pois nem sempre é retornado.
                $newToken['refresh_token'] = $refreshToken;
                $this->client->setAccessToken($newToken);
                Session::put('google_oauth_token', $newToken);
            } else {
                $this->handleTokenFailure();
            }
        } else {
            // Se não houver refresh_token, não é possível renovar.
            $this->handleTokenFailure();
        }
    }

    /**
     * Lida com falhas na renovação do token.
     *
     * Desloga o usuário, invalida a sessão e redireciona para a página de login
     * com uma mensagem informando que a sessão do Google expirou.
     */
    protected function handleTokenFailure(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        redirect()->route('login')
            ->with('message', 'Sua sessão do Google expirou. Faça login novamente.')
            ->with('messageType', 'warning')
            ->send();

        exit;
    }

    /**
     * Retorna a instância do cliente Google configurado.
     *
     * @return Google_Client A instância do cliente da API do Google.
     */
    public function getClient(): Google_Client
    {
        return $this->client;
    }
}
