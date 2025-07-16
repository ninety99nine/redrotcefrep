<?php

namespace App\Http\Controllers;

use App\Models\DeliveryMethod;
use App\Services\DeliveryMethodService;
use App\Http\Resources\DeliveryMethodResource;
use App\Http\Resources\DeliveryMethodResources;
use App\Http\Requests\DeliveryMethod\ShowDeliveryMethodsRequest;
use App\Http\Requests\DeliveryMethod\CreateDeliveryMethodRequest;
use App\Http\Requests\DeliveryMethod\UpdateDeliveryMethodRequest;
use App\Http\Requests\DeliveryMethod\DeleteDeliveryMethodsRequest;
use App\Http\Requests\DeliveryMethod\ShowDeliveryMethodRequest;
use App\Http\Requests\DeliveryMethod\DeleteDeliveryMethodRequest;
use App\Http\Requests\DeliveryMethod\UpdateDeliveryMethodArrangementRequest;
use App\Http\Requests\DeliveryMethod\ShowDeliveryMethodScheduleOptionsRequest;

class DeliveryMethodController extends Controller
{
    /**
     * @var DeliveryMethodService
     */
    protected $service;

    /**
     * DeliveryMethodController constructor.
     *
     * @param DeliveryMethodService $service
     */
    public function __construct(DeliveryMethodService $service)
    {
        $this->service = $service;
    }

    /**
     * Show delivery methods.
     *
     * @param ShowDeliveryMethodsRequest $request
     * @return DeliveryMethodResources|array
     */
    public function showDeliveryMethods(ShowDeliveryMethodsRequest $request): DeliveryMethodResources|array
    {
        return $this->service->showDeliveryMethods($request->validated());
    }

    /**
     * Create delivery method.
     *
     * @param CreateDeliveryMethodRequest $request
     * @return array
     */
    public function createDeliveryMethod(CreateDeliveryMethodRequest $request): array
    {
        return $this->service->createDeliveryMethod($request->validated());
    }

    /**
     * Delete multiple delivery methods.
     *
     * @param DeleteDeliveryMethodsRequest $request
     * @return array
     */
    public function deleteDeliveryMethods(DeleteDeliveryMethodsRequest $request): array
    {
        $deliveryMethodIds = request()->input('delivery_method_ids', []);
        return $this->service->deleteDeliveryMethods($deliveryMethodIds);
    }

    /**
     * Update delivery method arrangement.
     *
     * @param UpdateDeliveryMethodArrangementRequest $request
     * @return array
     */
    public function updateDeliveryMethodArrangement(UpdateDeliveryMethodArrangementRequest $request): array
    {
        return $this->service->updateDeliveryMethodArrangement($request->validated());
    }

    /**
     * Show delivery method schedule options.
     *
     * @param ShowDeliveryMethodScheduleOptionsRequest $request
     * @return array
     */
    public function showDeliveryMethodScheduleOptions(ShowDeliveryMethodScheduleOptionsRequest $request): array
    {
        return $this->service->showDeliveryMethodScheduleOptions($request->validated());
    }

    /**
     * Show single delivery method.
     *
     * @param ShowDeliveryMethodRequest $request
     * @param DeliveryMethod $deliveryMethod
     * @return DeliveryMethodResource
     */
    public function showDeliveryMethod(ShowDeliveryMethodRequest $request, DeliveryMethod $deliveryMethod): DeliveryMethodResource
    {
        return $this->service->showDeliveryMethod($deliveryMethod);
    }

    /**
     * Update delivery method.
     *
     * @param UpdateDeliveryMethodRequest $request
     * @param DeliveryMethod $deliveryMethod
     * @return array
     */
    public function updateDeliveryMethod(UpdateDeliveryMethodRequest $request, DeliveryMethod $deliveryMethod): array
    {
        return $this->service->updateDeliveryMethod($deliveryMethod, $request->validated());
    }

    /**
     * Delete delivery method.
     *
     * @param DeleteDeliveryMethodRequest $request
     * @param DeliveryMethod $deliveryMethod
     * @return array
     */
    public function deleteDeliveryMethod(DeleteDeliveryMethodRequest $request, DeliveryMethod $deliveryMethod): array
    {
        return $this->service->deleteDeliveryMethod($deliveryMethod);
    }
}
