<?php

namespace App\Enums;

enum InternshipStatus: string
{
    case PENDING = 'Pendente';
    case AWAITING_SIGNATURE = 'Aguardando Assinatura';
    case IN_PROGRESS = 'Em Andamento';
    case COMPLETED = 'Concluído';
    case CANCELLED = 'Cancelado';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendente',
            self::AWAITING_SIGNATURE => 'Aguardando Assinatura',
            self::IN_PROGRESS => 'Em Andamento',
            self::COMPLETED => 'Concluído',
            self::CANCELLED => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::AWAITING_SIGNATURE => 'info',
            self::IN_PROGRESS => 'primary',
            self::COMPLETED => 'success',
            self::CANCELLED => 'danger',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($status) => [$status->value => $status->label()])
            ->toArray();
    }
}
