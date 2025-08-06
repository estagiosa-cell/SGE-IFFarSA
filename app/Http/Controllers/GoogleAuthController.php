<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google_Client;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;

class GoogleAuthController extends Controller
{
    /**
     * Redireciona para o fluxo de autenticação do Google
     */
    public function redirect()
    {
        $client = new Google_Client();

        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));

        $client->setScopes([
            'https://www.googleapis.com/auth/drive',
            'https://www.googleapis.com/auth/spreadsheets',
            'https://www.googleapis.com/auth/documents',
        ]);

        $client->setAccessType('offline');
        $client->setPrompt('consent');

        return Redirect::to($client->createAuthUrl());
    }

    /**
     * Processa o callback de autenticação do Google
     */
    public function callback(Request $request)
    {
        if (!$request->has('code')) {
            Auth::logout();
            return redirect()->route('login')
                ->with('message', 'Autorização do Google foi cancelada.')
                ->with('messageType', 'warning');
        }

        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));

        $token = $client->fetchAccessTokenWithAuthCode($request->input('code'));

        if (isset($token['error'])) {
            Auth::logout();
            return redirect()->route('login')
                ->with('message', 'Erro ao conectar com o Google. Tente novamente.')
                ->with('messageType', 'danger');
        }

        $request->session()->put('google_oauth_token', $token);

        return redirect()->route('admin.dashboard')
            ->with('message', 'Conta Google conectada com sucesso!')
            ->with('messageType', 'success');
    }
}
