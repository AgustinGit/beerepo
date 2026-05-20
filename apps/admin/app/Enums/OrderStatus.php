<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Preparing = 'preparing';
    case ReadyForDelivery = 'ready_for_delivery';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Paid => 'Pagado',
            self::Preparing => 'En preparación',
            self::ReadyForDelivery => 'Listo para reparto',
            self::Shipped => 'Enviado',
            self::Delivered => 'Entregado',
            self::Cancelled => 'Cancelado',
        };
    }
}
