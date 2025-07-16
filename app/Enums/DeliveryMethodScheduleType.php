<?php

namespace App\Enums;

enum DeliveryMethodScheduleType: string
{
    case DATE = 'date';
    case DATE_AND_TIME = 'date and time';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
