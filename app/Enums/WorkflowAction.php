<?php

namespace App\Enums;

enum WorkflowAction: string
{
    case WHATSAPP_TEAM = 'whatsapp team';
    case WHATSAPP_CUSTOMER = 'whatsapp customer';
    case EMAIL_TEAM = 'email team';
    case EMAIL_CUSTOMER = 'email customer';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
