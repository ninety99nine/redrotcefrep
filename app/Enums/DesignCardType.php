<?php

namespace App\Enums;

enum DesignCardType: string
{
    case MAP = 'map';
    case TIPS = 'tips';
    case DATE = 'date';
    case TIME = 'time';
    case LOGO = 'logo';
    case LINK = 'link';
    case TEXT = 'text';
    case IMAGE = 'image';
    case VIDEO = 'video';
    case MEDIA = 'media';
    case ITEMS = 'items';
    case NUMBER = 'number';
    case BANNER = 'banner';
    case CONTACT = 'contact';
    case SOCIALS = 'socials';
    case DIVIDER = 'divider';
    case DELIVERY = 'delivery';
    case LOCATION = 'location';
    case CUSTOMER = 'customer';
    case CHECKBOX = 'checkbox';
    case PRODUCTS = 'products';
    case SELECTION = 'selection';
    case COUNTDOWN = 'countdown';
    case PROMO_CODE = 'promo_code';
    case CATEGORIES = 'categories';
    case INSTALL_APP = 'install_app';
    case LONG_ANSWER = 'long_answer';
    case SHORT_ANSWER = 'short_answer';
    case ORDER_SUMMARY = 'order_summary';
    case PAYMENT_METHODS = 'payment_methods';

    /**
     * Get all enum values.
     *
     * @return array
     */
    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
?>
