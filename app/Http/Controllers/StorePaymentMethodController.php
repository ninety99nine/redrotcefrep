<?php

namespace App\Http\Controllers;

use App\Models\StorePaymentMethod;
use App\Services\StorePaymentMethodService;
use App\Http\Resources\StorePaymentMethodResource;
use App\Http\Resources\StorePaymentMethodResources;
use App\Http\Requests\StorePaymentMethod\ShowStorePaymentMethodRequest;
use App\Http\Requests\StorePaymentMethod\ShowStorePaymentMethodsRequest;
use App\Http\Requests\StorePaymentMethod\CreateStorePaymentMethodRequest;
use App\Http\Requests\StorePaymentMethod\UpdateStorePaymentMethodRequest;
use App\Http\Requests\StorePaymentMethod\DeleteStorePaymentMethodRequest;
use App\Http\Requests\StorePaymentMethod\DeleteStorePaymentMethodsRequest;
use App\Http\Requests\StorePaymentMethod\UpdateStorePaymentMethodArrangementRequest;

class StorePaymentMethodController extends Controller
{
    /**
     * @var StorePaymentMethodService
     */
    protected $service;

    /**
     * StorePaymentMethodController constructor.
     *
     * @param StorePaymentMethodService $service
     */
    public function __construct(StorePaymentMethodService $service)
    {
        $this->service = $service;
    }

    /**
     * Show store payment methods.
     *
     * @param ShowStorePaymentMethodsRequest $request
     * @return StorePaymentMethodResources|array
     */
    public function showStorePaymentMethods(ShowStorePaymentMethodsRequest $request): StorePaymentMethodResources|array
    {
        return $this->service->showStorePaymentMethods($request->validated());
    }

    /**
     * Create store payment method.
     *
     * @param CreateStorePaymentMethodRequest $request
     * @return array
     */
    public function createStorePaymentMethod(CreateStorePaymentMethodRequest $request): array
    {
        return $this->service->createStorePaymentMethod($request->validated());
    }

    /**
     * Delete multiple store payment methods.
     *
     * @param DeleteStorePaymentMethodsRequest $request
     * @return array
     */
    public function deleteStorePaymentMethods(DeleteStorePaymentMethodsRequest $request): array
    {
        $storeId = request()->input('store_id');
        $storePaymentMethodIds = request()->input('store_payment_method_ids', []);
        return $this->service->deleteStorePaymentMethods($storeId, $storePaymentMethodIds);
    }

    /**
     * Update store payment method arrangement.
     *
     * @param UpdateStorePaymentMethodArrangementRequest $request
     * @return array
     */
    public function updateStorePaymentMethodArrangement(UpdateStorePaymentMethodArrangementRequest $request): array
    {
        return $this->service->updateStorePaymentMethodArrangement($request->validated());
    }

    /**
     * Show store payment method.
     *
     * @param ShowStorePaymentMethodRequest $request
     * @param StorePaymentMethod $storePaymentMethod
     * @return StorePaymentMethodResource
     */
    public function showStorePaymentMethod(ShowStorePaymentMethodRequest $request, StorePaymentMethod $storePaymentMethod): StorePaymentMethodResource
    {
        return $this->service->showStorePaymentMethod($storePaymentMethod);
    }

    /**
     * Update store payment method.
     *
     * @param UpdateStorePaymentMethodRequest $request
     * @param StorePaymentMethod $storePaymentMethod
     * @return array
     */
    public function updateStorePaymentMethod(UpdateStorePaymentMethodRequest $request, StorePaymentMethod $storePaymentMethod): array
    {
        return $this->service->updateStorePaymentMethod($storePaymentMethod, $request->validated());
    }

    /**
     * Delete store payment method.
     *
     * @param DeleteStorePaymentMethodRequest $request
     * @param StorePaymentMethod $storePaymentMethod
     * @return array
     */
    public function deleteStorePaymentMethod(DeleteStorePaymentMethodRequest $request, StorePaymentMethod $storePaymentMethod): array
    {
        return $this->service->deleteStorePaymentMethod($storePaymentMethod);
    }
}
