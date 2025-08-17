<?php

namespace App\Enums;

enum CacheName:string
{
    case USSD_HOME = 'ussd home';
    case CURRENCIES = 'currencies';
    case STORE_VISIT = 'store visit';
    case SHOPPING_CART = 'shopping cart';
    case IS_SUPER_ADMIN = 'is super admin';
    case IS_CUSTOMER_STATUS = 'is customer status';
    case CONVERTED_CURRENCY = 'converted currency';
    case SMS_ACCESS_TOKEN_RESPONSE = 'sms access token response';
    case TOTAL_ACTIVE_AUTO_BILLING_SCHEDULES = 'total active auto billing schedules';
    case AIRTIME_BILLING_ACCESS_TOKEN_RESPONSE = 'airtime billing access token response';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
