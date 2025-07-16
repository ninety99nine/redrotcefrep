<?php

namespace App\Enums;

enum DistanceUnit: string
{
    case KM = 'km';
    case MILE = 'mile';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
