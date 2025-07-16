<?php

namespace App\Enums;

enum InsightCategory: string
{
    case SALES = 'sales';
    case ORDERS = 'orders';
    case PRODUCTS = 'products';
    case CUSTOMERS = 'customers';
    case OPERATIONS = 'operations';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
