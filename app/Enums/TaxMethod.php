<?php

namespace App\Enums;

enum TaxMethod:string
{
    case INCLUSIVE = 'inclusive';
    case EXCLUSIVE = 'exclusive';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
