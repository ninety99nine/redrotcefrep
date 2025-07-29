<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Response;
use App\Services\StoreService;
use Illuminate\Contracts\View\View;
use App\Http\Resources\StoreResource;
use App\Http\Resources\StoreResources;
use App\Http\Requests\Store\ShowStoreRequest;
use App\Http\Requests\Store\ShowStoresRequest;
use App\Http\Requests\Store\CreateStoreRequest;
use App\Http\Requests\Store\UpdateStoreRequest;
use App\Http\Requests\Store\DeleteStoreRequest;
use App\Http\Requests\Store\DeleteStoresRequest;
use App\Http\Requests\Store\ShowStoreByAliasRequest;
use App\Http\Requests\Store\ShowStoreInsightsRequest;

class StoreController extends Controller
{
    /**
     * @var StoreService
     */
    protected $service;

    /**
     * StoreController constructor.
     *
     * @param StoreService $service
     */
    public function __construct(StoreService $service)
    {
        $this->service = $service;
    }

    /**
     * Show stores.
     *
     * @param ShowStoresRequest $request
     * @return StoreResources|array
     */
    public function showStores(ShowStoresRequest $request): StoreResources|array
    {
        return $this->service->showStores($request->validated());
    }

    /**
     * Create store.
     *
     * @param CreateStoreRequest $request
     * @return array
     */
    public function createStore(CreateStoreRequest $request): array
    {
        return $this->service->createStore($request->validated());
    }

    /**
     * Delete multiple stores.
     *
     * @param DeleteStoresRequest $request
     * @return array
     */
    public function deleteStores(DeleteStoresRequest $request): array
    {
        $storeIds = request()->input('store_ids', []);
        return $this->service->deleteStores($storeIds);
    }

    /**
     * Show store by alias.
     *
     * @param ShowStoreByAliasRequest $request
     * @param string $alias
     * @return StoreResource
     */
    public function showStoreByAlias(ShowStoreByAliasRequest $request, string $alias): StoreResource
    {
        return $this->service->showStoreByAlias($alias);
    }

    /**
     * Show store.
     *
     * @param ShowStoreRequest $request
     * @param Store $store
     * @return StoreResource
     */
    public function showStore(ShowStoreRequest $request, Store $store): StoreResource
    {
        return $this->service->showStore($store);
    }

    /**
     * Update store.
     *
     * @param UpdateStoreRequest $request
     * @param Store $store
     * @return array
     */
    public function updateStore(UpdateStoreRequest $request, Store $store): array
    {
        return $this->service->updateStore($store, $request->validated());
    }

    /**
     * Delete store.
     *
     * @param DeleteStoreRequest $request
     * @param Store $store
     * @return array
     */
    public function deleteStore(DeleteStoreRequest $request, Store $store): array
    {
        return $this->service->deleteStore($store);
    }

    /**
     * Show store insights.
     *
     * @param ShowStoreInsightsRequest $request
     * @param Store $store
     * @return array
     */
    public function showStoreInsights(ShowStoreInsightsRequest $request, Store $store): array
    {
        return $this->service->showStoreInsights($store, $request->validated());
    }

    /**
     * Show store QR code.
     *
     * @param Store $store
     * @return Response
     */
    public function showStoreQrCode(Store $store): Response
    {
        return $this->service->showStoreQrCode($store);
    }

    /**
     * Show store QR code preview.
     *
     * @param Store $store
     * @return View
     */
    public function showStoreQrCodePreview(Store $store): View
    {
        return $this->service->showStoreQrCodePreview($store);
    }
}
