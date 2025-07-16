<?php

namespace App\Enums;

enum SmsStatus:string {
    case SENT = 'sent';
    case PENDING = 'pending';
    case FAILED_SENDING = 'failed sending';
    case DELIVERED_VERIFIED = 'delivery verified';
    case FAILED_DELIVERY_VERIFICATION = 'failed delivery verification';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
