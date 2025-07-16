<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Services\AddressService;
use App\Http\Resources\AddressResource;
use App\Http\Resources\AddressResources;
use App\Http\Requests\Address\ShowAddressRequest;
use App\Http\Requests\Address\ShowAddressesRequest;
use App\Http\Requests\Address\CreateAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Http\Requests\Address\DeleteAddressRequest;
use App\Http\Requests\Address\DeleteAddressesRequest;
use App\Http\Requests\Address\ShowCountryAddressOptionsRequest;

class AddressController extends Controller
{
    /**
     * @var AddressService
     */
    protected $service;

    /**
     * AddressController constructor.
     *
     * @param AddressService $service
     */
    public function __construct(AddressService $service)
    {
        $this->service = $service;
    }

    /**
     * Show addresses.
     *
     * @param ShowAddressesRequest $request
     * @return AddressResources|array
     */
    public function showAddresses(ShowAddressesRequest $request): AddressResources|array
    {
        return $this->service->showAddresses($request->validated());
    }

    /**
     * Create address.
     *
     * @param CreateAddressRequest $request
     * @return array
     */
    public function createAddress(CreateAddressRequest $request): array
    {
        return $this->service->createAddress($request->validated());
    }

    /**
     * Delete multiple addresses.
     *
     * @param DeleteAddressesRequest $request
     * @return array
     */
    public function deleteAddresses(DeleteAddressesRequest $request): array
    {
        $addressIds = request()->input('address_ids', []);
        return $this->service->deleteAddresses($addressIds);
    }

    /**
     * Show country address options.
     *
     * @param ShowCountryAddressOptionsRequest $request
     * @return array
     */
    public function showCountryAddressOptions(ShowCountryAddressOptionsRequest $request): array
    {
        return $this->service->showCountryAddressOptions();
    }

    /**
     * Show single address.
     *
     * @param ShowAddressRequest $request
     * @param Address $address
     * @return AddressResource
     */
    public function showAddress(ShowAddressRequest $request, Address $address): AddressResource
    {
        return $this->service->showAddress($address);
    }

    /**
     * Update address.
     *
     * @param UpdateAddressRequest $request
     * @param Address $address
     * @return array
     */
    public function updateAddress(UpdateAddressRequest $request, Address $address): array
    {
        return $this->service->updateAddress($address, $request->validated());
    }

    /**
     * Delete address.
     *
     * @param DeleteAddressRequest $request
     * @param Address $address
     * @return array
     */
    public function deleteAddress(DeleteAddressRequest $request, Address $address): array
    {
        return $this->service->deleteAddress($address);
    }
}
