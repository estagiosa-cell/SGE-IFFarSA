<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case COORDENADOR = 'coordenador';
    case ORIENTADOR = 'orientador';

    /**
     * Retorna o label legível do papel
     */
    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrador',
            self::COORDENADOR => 'Coordenador',
            self::ORIENTADOR => 'Orientador',
        };
    }

    /**
     * Retorna todos os valores como array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
