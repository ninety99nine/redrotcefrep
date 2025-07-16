<?php

namespace App\Enums;

enum WeightUnit: string
{
    case GRAM = 'g';
    case POUND = 'lb';
    case OUNCE = 'oz';
    case KILOGRAM = 'kg';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
