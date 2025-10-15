<?php

namespace App\Enums;

enum AnalyticsType: string
{
    case TOP_PAGES = 'top_pages';
    case PAGE_VIEWS = 'page_views';
    case STORE_VISITORS = 'store_visitors';


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
