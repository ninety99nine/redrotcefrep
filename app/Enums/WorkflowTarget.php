<?php

namespace App\Enums;

enum WorkflowTarget: string
{
    case ORDER = 'order';
    case PAYMENT = 'payment';
    case PRODUCT = 'product';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
