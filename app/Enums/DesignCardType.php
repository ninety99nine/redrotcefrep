<?php

namespace App\Enums;

enum DesignCardType: string
{
    case STOREFRONT = 'storefront';
    case CHECKOUT = 'checkout';
    case PAYMENTS = 'payments';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
