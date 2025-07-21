<?php

namespace App\Enums;

enum CourseType: string
{
    case TECNICO = 'tecnico';
    case BACHARELADO = 'bacharelado';
    case LICENCIATURA = 'licenciatura';
    case TECNOLOGIA = 'tecnologia';
    case ESPECIALIZACAO = 'especializacao';
    case MESTRADO = 'mestrado';
    case DOUTORADO = 'doutorado';
    case SEQUENCIAL = 'sequencial';
    case FIC = 'fic'; // Formação Inicial e Continuada

    /**
     * Retorna o label legível do tipo
     */
    public function label(): string
    {
        return match ($this) {
            self::TECNICO => 'Técnico',
            self::BACHARELADO => 'Bacharelado',
            self::LICENCIATURA => 'Licenciatura',
            self::TECNOLOGIA => 'Tecnologia',
            self::ESPECIALIZACAO => 'Especialização',
            self::MESTRADO => 'Mestrado',
            self::DOUTORADO => 'Doutorado',
            self::SEQUENCIAL => 'Sequencial',
            self::FIC => 'Formação Inicial e Continuada',
        };
    }

    /**
     * Retorna todos os valores como array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Retorna os tipos válidos para um determinado nível
     */
    public static function forLevel(CourseLevel $level): array
    {
        return match ($level) {
            CourseLevel::MEDIO => [
                self::TECNICO,
                self::FIC,
            ],
            CourseLevel::SUPERIOR => [
                self::BACHARELADO,
                self::LICENCIATURA,
                self::TECNOLOGIA,
                self::SEQUENCIAL,
            ],
            CourseLevel::POS_GRADUACAO => [
                self::ESPECIALIZACAO,
                self::MESTRADO,
                self::DOUTORADO,
            ],
        };
    }

    /**
     * Verifica se um tipo é válido para um nível
     */
    public static function isValidForLevel(CourseType $type, CourseLevel $level): bool
    {
        $validTypes = self::forLevel($level);
        return in_array($type, $validTypes);
    }
}
