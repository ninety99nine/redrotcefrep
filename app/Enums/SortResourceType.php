<?php

namespace App\Enums;

enum SortResourceType:string
{
    case ORDERS = 'orders';
    case PRODUCTS = 'products';
    case CUSTOMERS = 'customers';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
