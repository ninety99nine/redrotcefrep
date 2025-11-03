<?php

namespace App\Enums;

enum FilterResourceType:string
{
    case ORDERS = 'orders';
    case REVIEWS = 'reviews';
    case PRODUCTS = 'products';
    case CUSTOMERS = 'customers';
    case PROMOTIONS = 'promotions';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
