<?php

namespace App\Services;

use Exception;
use App\Models\DeliveryAddress;
use App\Http\Resources\DeliveryAddressResource;
use App\Http\Resources\DeliveryAddressResources;

class DeliveryAddressService extends BaseService
{
    /**
     * Show delivery addresses.
     *
     * @param array $data
     * @return DeliveryAddressResources|array
     */
    public function showDeliveryAddresses(array $data): DeliveryAddressResources|array
    {
        $query = DeliveryAddress::query()->when(!request()->has('_sort'), fn($query) => $query->latest());

        if (isset($data['order_id'])) {
            $query->where('order_id', $data['order_id']);
        }

        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create delivery address.
     *
     * @param array $data
     * @return array
     */
    public function createDeliveryAddress(array $data): array
    {
        $deliveryAddress = DeliveryAddress::create($data);
        return $this->showCreatedResource($deliveryAddress);
    }

    /**
     * Delete delivery addresses.
     *
     * @param array $deliveryAddressIds
     * @return array
     * @throws Exception
     */
    public function deleteDeliveryAddresses(array $deliveryAddressIds): array
    {
        $deliveryAddresses = DeliveryAddress::whereIn('id', $deliveryAddressIds)->get();

        if ($totalAddresses = $deliveryAddresses->count()) {

            foreach ($deliveryAddresses as $deliveryAddress) {

                $this->deleteDeliveryAddress($deliveryAddress);

            }

            return ['message' => $totalAddresses . ($totalAddresses == 1 ? ' Delivery Address' : ' Delivery Addresses') . ' deleted'];
        } else {
            throw new Exception('No Delivery Addresses deleted');
        }
    }

    /**
     * Show delivery address.
     *
     * @param DeliveryAddress $deliveryAddress
     * @return DeliveryAddressResource
     */
    public function showDeliveryAddress(DeliveryAddress $deliveryAddress): DeliveryAddressResource
    {
        return $this->showResource($deliveryAddress);
    }

    /**
     * Update delivery address.
     *
     * @param DeliveryAddress $deliveryAddress
     * @param array $data
     * @return array
     */
    public function updateDeliveryAddress(DeliveryAddress $deliveryAddress, array $data): array
    {
        $deliveryAddress->update($data);
        return $this->showUpdatedResource($deliveryAddress);
    }

    /**
     * Delete delivery address.
     *
     * @param DeliveryAddress $deliveryAddress
     * @return array
     * @throws Exception
     */
    public function deleteDeliveryAddress(DeliveryAddress $deliveryAddress): array
    {
        $deleted = $deliveryAddress->delete();

        return [
            'deleted' => $deleted,
            'message' => $deleted ? 'Delivery Address deleted' : 'Delivery Address delete unsuccessful'
        ];
    }
}
