<?php

namespace App\Enums;

enum DomainStatus: string
{
    case PENDING = 'pending';
    case CONNECTED = 'connected';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
