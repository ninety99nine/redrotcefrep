<?php

namespace App\Enums;

enum AutoGenerateTimeSlotsUnit: string
{
    case HOUR = 'hour';
    case MINUTE = 'minute';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
