<?php

namespace App\Enums;

enum DomainStatus: string
{
    case PENDING = 'pending';
    case CONNECTED = 'connected';
    case PROCESSING = 'processing';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
