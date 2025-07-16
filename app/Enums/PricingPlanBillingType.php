<?php

namespace App\Enums;

enum PricingPlanBillingType: string
{
    case ONE_TIME = 'one time';
    case RECURRING = 'recurring';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
