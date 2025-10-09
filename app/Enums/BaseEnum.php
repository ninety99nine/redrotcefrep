<?php

namespace App\Enums;

enum BaseEnum
{
    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
