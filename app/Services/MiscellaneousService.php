<?php

namespace App\Services;

use App\Models\Store;
use App\Enums\SocialMediaLink;
use App\Enums\SortResourceType;
use App\Enums\FilterResourceType;

class MiscellaneousService
{
    /**
     * Show social media links.
     *
     * @return array
     */
    public function showSocialMediaLinks(): array
    {
        return collect(SocialMediaLink::values())->map(function($socialMediaPlatform) {
            return [
                'name' => $socialMediaPlatform,
                'icon' => asset('/images/social-media-icons/'.strtolower($socialMediaPlatform).'.png')
            ];
        })->toArray();
    }

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
}
