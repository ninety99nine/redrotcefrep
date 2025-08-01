<?php

namespace App\Enums;

enum ProductUnitType: string
{
    case GRAM = 'g';
    case KILOGRAM = 'kg';
    case LITER = 'l';
    case MILLILITER = 'ml';
    case PIECE = 'psc';
    case PASSENGER = 'pax';
    case PERSON = 'person';
    case ROOM = 'room';
    case PACK = 'pack';
    case QUANTITY = 'qty';
    case POUND = 'lbs';
    case HOUR = 'hour';
    case BOX = 'box';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
