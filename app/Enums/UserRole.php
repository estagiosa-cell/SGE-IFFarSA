<?php

namespace App\Enums;

/**
 * Enum que representa os perfis/papéis de usuário no sistema.
 *
 * Define os diferentes níveis de acesso e permissões que um usuário pode ter.
 */
enum UserRole: string
{
    case ADMIN = 'admin';
    case COORDENADOR = 'coordenador';
    case ORIENTADOR = 'orientador';
    case DIRECAO_ENSINO = 'direcao_ensino';

    /**
     * Retorna o label legível do papel do usuário.
     *
     * @return string O texto de exibição do papel.
     */
    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrador',
            self::COORDENADOR => 'Coordenador',
            self::ORIENTADOR => 'Orientador',
            self::DIRECAO_ENSINO => 'Direção de Ensino',
        };
    }

    /**
     * Retorna todos os valores possíveis do enum como array.
     *
     * @return array Array com os valores das roles (admin, coordenador, orientador).
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
