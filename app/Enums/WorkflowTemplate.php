<?php

namespace App\Enums;

enum WorkflowTemplate: string
{
    case ORDER_DETAILS = 'order details';
    case REQUEST_REVIEW = 'request review';
    case PAYMENT_REMINDER = 'payment reminder';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
