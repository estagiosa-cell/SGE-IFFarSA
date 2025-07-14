<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;

/**
 * Serviço responsável por determinar o redirecionamento após login
 * baseado no perfil/permissões do usuário.
 */
class LoginRedirectService
{
    /**
     * Determina o nome da rota para onde o utilizador deve ser redirecionado.
     *
     * Este método verifica as permissões do usuário na ordem definida no array $routes
     * e retorna a primeira rota correspondente ao perfil encontrado.
     *
     * @param Authenticatable $user O usuário autenticado
     * @return string Nome da rota para redirecionamento
     *
     * @note A ordem das permissões no array importa - verifica da primeira para a última
     * @note Se nenhuma permissão for encontrada, redireciona para página de erro
     */
    public function getRedirectRoute(Authenticatable $user): string
    {
        // Mapeamento de permissões para rotas de destino
        // Adicione novos perfis aqui conforme necessário
        $routes = [
            'is-admin' => 'dashboard',
            'is-coordenador' => 'estagios',
            'is-orientador' => 'estagios',
        ];

        // Itera pelas permissões até encontrar uma válida
        foreach ($routes as $permission => $route) {
            if (Gate::allows($permission, $user)) {
                return $route;
            }
        }

        // Fallback: usuário sem perfil válido
        logger()->warning('Usuário sem perfil válido tentou fazer login', [
            'user_id' => $user->getAuthIdentifier(),
            'email' => $user->getAuthIdentifierName(),
            'timestamp' => now()
        ]);

        abort(403, 'Acesso negado: Sua conta não possui um perfil válido para acessar o sistema. Entre em contato com o administrador.');
    }
}
