<?php

namespace App\Enums;

enum EmailVerificationType:string {
    case REGISTRATION_EMAIL = 'registration email';
    case UPDATED_EMAIL = 'updated email';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
