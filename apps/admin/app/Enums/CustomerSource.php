<?php

declare(strict_types=1);

namespace App\Enums;

enum CustomerSource: string
{
    case Referido = 'referido';
    case Cata = 'cata';
    case Redes = 'redes';
    case Organico = 'organico';

    public function label(): string
    {
        return match ($this) {
            self::Referido => 'Referido',
            self::Cata => 'Cata',
            self::Redes => 'Redes sociales',
            self::Organico => 'Orgánico',
        };
    }
}
