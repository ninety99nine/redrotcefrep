<?php

namespace App\Enums;

enum OrderPaymentStatus:string {
    case PAID = 'paid';
    case UNPAID = 'unpaid';
    case PARTIALLY_PAID = 'partially paid';
    case PENDING_PAYMENT = 'pending payment';
    case CONFIRMING_PAYMENT = 'confirming payment';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
