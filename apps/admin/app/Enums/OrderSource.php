<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderSource: string
{
    case Web = 'web';
    case Admin = 'admin';
    case Club = 'club';
    case Cata = 'cata';

    public function label(): string
    {
        return match ($this) {
            self::Web => 'Web',
            self::Admin => 'Admin',
            self::Club => 'Club',
            self::Cata => 'Cata',
        };
    }
}
