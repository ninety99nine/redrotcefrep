<?php

namespace App\Http\Controllers;

use App\Services\MiscellaneousService;
use App\Http\Requests\Miscellaneous\ShowFiltersRequest;
use App\Http\Requests\Miscellaneous\ShowSortingRequest;
use App\Http\Requests\Miscellaneous\InspectShoppingCartRequest;

class MiscellaneousController extends Controller
{
    /**
     * @var MiscellaneousService
     */
    protected $service;

    /**
     * MiscellaneousController constructor.
     *
     * @param MiscellaneousService $service
     */
    public function __construct(MiscellaneousService $service)
    {
        $this->service = $service;
    }

    /**
     * Inspect shopping cart.
     *
     * @param InspectShoppingCartRequest $request
     * @return array
     */
    public function inspectShoppingCart(InspectShoppingCartRequest $request): array
    {
        return $this->service->inspectShoppingCart($request->validated());
    }

    /**
     * Show social media links.
     *
     * @return array
     */
    public function showSocialMediaLinks(): array
    {
        return $this->service->showSocialMediaLinks();
    }

    /**
     * Show filters.
     *
     * @param ShowFiltersRequest $request
     * @return JsonResponse
     */
    public function showFilters(ShowFiltersRequest $request): array
    {
        return $this->service->showFilters($request->validated());
    }

    /**
     * Show sorting.
     *
     * @param ShowSortingRequest $request
     * @return JsonResponse
     */
    public function showSorting(ShowSortingRequest $request): array
    {
        return $this->service->showSorting($request->validated());
    }
}
