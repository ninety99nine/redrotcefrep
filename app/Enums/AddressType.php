<?php

namespace App\Enums;

enum AddressType:string {
    case HOME = 'home';
    case WORK = 'work';
    case OTHER = 'other';
    case BUSINESS = 'business';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
