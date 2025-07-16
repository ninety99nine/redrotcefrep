<?php

namespace App\Enums;

enum SmsFailureType:string {
    case InternalFailure = 'internal failure';
    case MessageSendingFailed = 'message sending failed';
    case TokenGenerationFailed = 'token generation failed';
    case MessageDeliveryVerificationFailed = 'message delivery verification failed';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
