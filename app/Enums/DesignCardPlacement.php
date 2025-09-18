<?php

namespace App\Enums;

enum DesignCardPlacement: string
{
    case STOREFRONT = 'storefront';
    case CHECKOUT = 'checkout';
    case PAYMENT = 'payment';
    case MENU = 'menu';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
