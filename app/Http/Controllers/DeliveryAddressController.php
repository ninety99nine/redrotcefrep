<?php

namespace App\Http\Controllers;

use App\Models\DeliveryAddress;
use App\Services\DeliveryAddressService;
use App\Http\Resources\DeliveryAddressResource;
use App\Http\Resources\DeliveryAddressResources;
use App\Http\Requests\DeliveryAddress\ShowDeliveryAddressRequest;
use App\Http\Requests\DeliveryAddress\ShowDeliveryAddressesRequest;
use App\Http\Requests\DeliveryAddress\CreateDeliveryAddressRequest;
use App\Http\Requests\DeliveryAddress\UpdateDeliveryAddressRequest;
use App\Http\Requests\DeliveryAddress\DeleteDeliveryAddressRequest;
use App\Http\Requests\DeliveryAddress\DeleteDeliveryAddressesRequest;

class DeliveryAddressController extends Controller
{
    /**
     * @var DeliveryAddressService
     */
    protected $service;

    /**
     * DeliveryAddressController constructor.
     *
     * @param DeliveryAddressService $service
     */
    public function __construct(DeliveryAddressService $service)
    {
        $this->service = $service;
    }

    /**
     * Show delivery addresses.
     *
     * @param ShowDeliveryAddressesRequest $request
     * @return DeliveryAddressResources|array
     */
    public function showDeliveryAddresses(ShowDeliveryAddressesRequest $request): DeliveryAddressResources|array
    {
        return $this->service->showDeliveryAddresses($request->validated());
    }

    /**
     * Create delivery address.
     *
     * @param CreateDeliveryAddressRequest $request
     * @return array
     */
    public function createDeliveryAddress(CreateDeliveryAddressRequest $request): array
    {
        return $this->service->createDeliveryAddress($request->validated());
    }

    /**
     * Delete multiple delivery addresses.
     *
     * @param DeleteDeliveryAddressesRequest $request
     * @return array
     */
    public function deleteDeliveryAddresses(DeleteDeliveryAddressesRequest $request): array
    {
        $deliveryAddressIds = request()->input('delivery_address_ids', []);
        return $this->service->deleteDeliveryAddresses($deliveryAddressIds);
    }

    /**
     * Show delivery address.
     *
     * @param ShowDeliveryAddressRequest $request
     * @param DeliveryAddress $deliveryAddress
     * @return DeliveryAddressResource
     */
    public function showDeliveryAddress(ShowDeliveryAddressRequest $request, DeliveryAddress $deliveryAddress): DeliveryAddressResource
    {
        return $this->service->showDeliveryAddress($deliveryAddress);
    }

    /**
     * Update delivery address.
     *
     * @param UpdateDeliveryAddressRequest $request
     * @param DeliveryAddress $deliveryAddress
     * @return array
     */
    public function updateDeliveryAddress(UpdateDeliveryAddressRequest $request, DeliveryAddress $deliveryAddress): array
    {
        return $this->service->updateDeliveryAddress($deliveryAddress, $request->validated());
    }

    /**
     * Delete delivery address.
     *
     * @param DeleteDeliveryAddressRequest $request
     * @param DeliveryAddress $deliveryAddress
     * @return array
     */
    public function deleteDeliveryAddress(DeleteDeliveryAddressRequest $request, DeliveryAddress $deliveryAddress): array
    {
        return $this->service->deleteDeliveryAddress($deliveryAddress);
    }
}
