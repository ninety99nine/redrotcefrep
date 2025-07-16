<?php

namespace App\Enums;

enum Platform: string
{
    case WEB = 'web';
    case USSD = 'ussd';
    case MOBILE = 'mobile';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
