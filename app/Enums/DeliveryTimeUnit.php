<?php

namespace App\Enums;

enum DeliveryTimeUnit: string
{
    case DAY = 'day';
    case HOUR = 'hour';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
