<?php

namespace App\Enums;

/**
 * Enum que representa os possíveis status de um estágio.
 *
 * Define os estados pelos quais um estágio pode passar durante seu ciclo de vida,
 * incluindo labels legíveis e cores para exibição na interface.
 */
enum InternshipStatus: string
{
    case PENDING = 'Pendente';
    case AWAITING_SIGNATURE = 'Aguardando Assinatura';
    case RELEASED = 'Liberado';
    case IN_PROGRESS = 'Em Andamento';
    case COMPLETED = 'Concluído';
    case CANCELLED = 'Cancelado';

    /**
     * Retorna o label legível do status.
     *
     * @return string O texto de exibição do status.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendente',
            self::AWAITING_SIGNATURE => 'Aguardando Assinatura',
            self::RELEASED => 'Liberado',
            self::IN_PROGRESS => 'Em Andamento',
            self::COMPLETED => 'Concluído',
            self::CANCELLED => 'Cancelado',
        };
    }

    /**
     * Retorna a cor do Bootstrap associada ao status.
     *
     * Usado para estilizar badges e alertas na interface do usuário.
     *
     * @return string A classe de cor do Bootstrap (warning, info, primary, success, danger).
     */
    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::AWAITING_SIGNATURE => 'info',
            self::RELEASED => 'secondary',
            self::IN_PROGRESS => 'primary',
            self::COMPLETED => 'success',
            self::CANCELLED => 'danger',
        };
    }

    /**
     * Retorna todos os status como um array associativo para uso em selects.
     *
     * @return array Array no formato ['valor' => 'label'].
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($status) => [$status->value => $status->label()])
            ->toArray();
    }
}
