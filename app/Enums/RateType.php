<?php

namespace App\Enums;

enum RateType: string
{
    case FLAT = 'flat';
    case PERCENTAGE = 'percentage';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
