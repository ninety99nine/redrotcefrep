<?php

namespace App\Enums;

enum SystemRole: string
{
    case REGULAR = 'regular';
    case SUPER_ADMIN = 'super admin';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
