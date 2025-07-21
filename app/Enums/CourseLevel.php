<?php

namespace App\Enums;

enum CourseLevel: string
{
    case MEDIO = 'medio';
    case SUPERIOR = 'superior';
    case POS_GRADUACAO = 'pos_graduacao';

    /**
     * Retorna o label legível do nível
     */
    public function label(): string
    {
        return match ($this) {
            self::MEDIO => 'Ensino Médio',
            self::SUPERIOR => 'Ensino Superior',
            self::POS_GRADUACAO => 'Pós-Graduação'
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
