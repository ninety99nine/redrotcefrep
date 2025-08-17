<?php

namespace App\Enums;

enum TagType: string
{
    case PRODUCT = 'product';
    case CUSTOMER = 'customer';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
