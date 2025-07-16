<?php

namespace App\Enums;

enum AllowedQuantityPerOrder: string
{
    case LIMITED = 'limited';
    case UNLIMITED = 'unlimited';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
