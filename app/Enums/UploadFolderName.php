<?php

namespace App\Enums;

enum UploadFolderName:string
{
    case QR_CODES = 'qr-codes';
    case STORE_LOGO = 'store_logo';
    case PROFILE_PHOTO = 'profile_photo';
    case PRODUCT_PHOTO = 'product_photo';
    case STORE_PAYMENT_METHOD_LOGO = 'store_payment_method_logo';
    case STORE_PAYMENT_METHOD_PHOTO = 'store_payment_method_photo';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
