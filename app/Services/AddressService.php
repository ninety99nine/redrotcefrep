<?php

namespace App\Services;

use Exception;
use App\Models\Address;
use App\Http\Resources\AddressResource;
use App\Http\Resources\AddressResources;

class AddressService extends BaseService
{
    /**
     * Show addresses.
     *
     * @param array $data
     * @return AddressResources|array
     */
    public function showAddresses(array $data): AddressResources|array
    {
        $query = Address::query()->when(!request()->has('_sort'), fn($query) => $query->latest());
        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create address.
     *
     * @param array $data
     * @return array
     */
    public function createAddress(array $data): array
    {
        $address = Address::create($data);
        return $this->showCreatedResource($address);
    }

    /**
     * Delete Addresses.
     *
     * @param array $addressIds
     * @return array
     * @throws Exception
     */
    public function deleteAddresses(array $addressIds): array
    {
        $addresses = Address::whereIn('id', $addressIds)->get();

        if ($totalAddresses = $addresses->count()) {

            foreach ($addresses as $address) {
                $address->delete();
            }

            return ['message' => $totalAddresses . ($totalAddresses == 1 ? ' Address' : ' Addresses') . ' deleted'];

        } else {
            throw new Exception('No Addresses deleted');
        }
    }

    /**
     * Validate address.
     *
     * @param array $data
     * @return AddressResource
     */
    public function validateAddress(array $data): AddressResource
    {
        $address = new Address($data);
        return $this->showResource($address);
    }

    /**
     * Delete Addresses.
     *
     * @return array
     */
    public function showCountryAddressOptions(): array
    {
        return (new CountryAddressService)->getCountryAddressOptions();
    }

    /**
     * Show address.
     *
     * @param Address $address
     * @return AddressResource
     */
    public function showAddress(Address $address): AddressResource
    {
        return $this->showResource($address);
    }

    /**
     * Update address.
     *
     * @param Address $address
     * @param array $data
     * @return array
     */
    public function updateAddress(Address $address, array $data): array
    {
        $address->update($data);
        return $this->showUpdatedResource($address);
    }

    /**
     * Delete address.
     *
     * @param Address $address
     * @return array
     * @throws Exception
     */
    public function deleteAddress(Address $address): array
    {
        $deleted = $address->delete();

        if ($deleted) {
            return ['message' => 'Address deleted'];
        } else {
            throw new Exception('Address delete unsuccessful');
        }
    }
}
