<?php

declare(strict_types=1);

namespace App\Enums;

enum CouponApplicableTo: string
{
    case All = 'all';
    case PremiumOnly = 'premium_only';
    case BasicOnly = 'basic_only';
    case FirstPurchase = 'first_purchase';

    public function label(): string
    {
        return match ($this) {
            self::All => 'Todos',
            self::PremiumOnly => 'Solo Premium',
            self::BasicOnly => 'Solo Básico',
            self::FirstPurchase => 'Primera compra',
        };
    }
}
