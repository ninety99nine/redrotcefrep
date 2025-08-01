<?php

namespace App\Enums;

enum ProductType: string
{
    case PHYSICAL = 'physical';
    case DIGITAL = 'digital';
    case BOOKING = 'booking';
    case SUBSCRIPTION = 'subscription';
    case OTHER = 'other';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
