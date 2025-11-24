<?php

namespace App\Enums;

enum TransactionPaymentStatus:string
{
    case PAID = 'paid';
    case FAILED_PAYMENT = 'failed payment';
    case PENDING_PAYMENT = 'pending payment';
    case WAITING_CONFIRMATION = 'waiting confirmation';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
