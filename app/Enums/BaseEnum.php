<?php

namespace App\Enums;

enum InsightPeriod
{
    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
