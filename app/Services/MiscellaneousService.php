<?php

namespace App\Services;

use App\Models\Store;
use App\Enums\SocialMediaLink;
use App\Enums\SortResourceType;
use App\Enums\FilterResourceType;

class MiscellaneousService
{
    /**
     * Show filters.
     *
     * @param array $data
     * @return array
     */
    public function showFilters(array $data): array
    {
        $type = $data['type'];
        $storeId = $data['store_id'] ?? null;
        $store = $storeId ? Store::find($storeId) : null;

        $filterService = new FilterService;
        if(!empty($store)) $filterService->setStore($store);
        $filterResourceType = FilterResourceType::tryFrom($type);

        return $filterService->getFiltersByResourceType($filterResourceType);
    }

    /**
     * Show sorting.
     *
     * @param array $data
     * @return array
     */
    public function showSorting(array $data): array
    {
        $type = $data['type'];

        $sortingService = new SortingService;
        $sortResourceType = SortResourceType::tryFrom($type);

        return $sortingService->getSortingOptionsByResourceType($sortResourceType);
    }

    /**
     * Convert currency.
     *
     * @param array $data
     * @return array
     */
    public function convertCurrency(array $data): array
    {
        $to = $data['to'];
        $from = $data['from'];
        $amount = $data['amount'];

        $moneyService = new MoneyService;
        return $moneyService->convertCurrency($amount, $from, $to);
    }
}
