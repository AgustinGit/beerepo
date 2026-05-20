<?php

declare(strict_types=1);

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Cancelled = 'cancelled';
    case PastDue = 'past_due';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activa',
            self::Paused => 'Pausada',
            self::Cancelled => 'Cancelada',
            self::PastDue => 'Pago pendiente',
        };
    }
}
