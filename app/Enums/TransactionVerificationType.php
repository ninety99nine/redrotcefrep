<?php

namespace App\Enums;

enum TransactionVerificationType:string
{
    case MANUAL = 'manual';
    case AUTOMATIC = 'automatic';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
