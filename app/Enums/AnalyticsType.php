<?php

namespace App\Enums;

enum AnalyticsType: string
{
    case TOP_PAGES = 'top_pages';
    case PAGE_VIEWS = 'page_views';
    case STORE_VISITORS = 'store_visitors';
    case SALES_OVER_TIME = 'sales_over_time';
    case ORDERS_OVER_TIME = 'orders_over_time';
    case DELIVERY_BY_ORDERS = 'delivery_by_orders';
    case AVERAGE_ORDER_VALUE = 'average_order_value';
    case DELIVERY_BY_DELIVERY_TIME = 'delivery_by_delivery_time';


    /**
     * Get all enum values.
     *
     * @return array
     */
    public static function values(): array
    {
        return array_map(fn(self $type) => $type->value, self::cases());
    }
}
