<?php

namespace App\Enums;

enum DomainType: string
{
    case EXISTING = 'existing';
    case PURCHASED = 'purchased';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
