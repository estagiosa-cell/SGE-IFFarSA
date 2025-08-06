<?php

namespace App\Services;

use Google_Client;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class GoogleApiService
{
    public Google_Client $client;

    /**
     * Inicializa o cliente Google com configuração e token da sessão
     */
    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect'));

        if (Session::has('google_oauth_token')) {
            $token = Session::get('google_oauth_token');
            $this->client->setAccessToken($token);

            if ($this->client->isAccessTokenExpired()) {
                $this->refreshToken();
            }
        }
    }

    /**
     * Renova o token de acesso usando o refresh_token
     */
    protected function refreshToken(): void
    {
        $refreshToken = $this->client->getRefreshToken();

        if ($refreshToken) {
            $newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);

            if (!isset($newToken['error'])) {
                $newToken['refresh_token'] = $refreshToken;
                $this->client->setAccessToken($newToken);
                Session::put('google_oauth_token', $newToken);
            } else {
                $this->handleTokenFailure();
            }
        } else {
            $this->handleTokenFailure();
        }
    }

    /**
     * Lida com falhas na renovação do token
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
    }

    /**
     * Retorna a instância do cliente Google configurado
     *
     * @return Google_Client
     */
    public function getClient(): Google_Client
    {
        return $this->client;
    }
}
