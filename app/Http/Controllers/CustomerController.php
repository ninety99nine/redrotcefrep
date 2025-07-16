<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\CustomerService;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\CustomerResources;
use App\Http\Requests\Customer\ShowCustomerRequest;
use App\Http\Requests\Customer\ShowCustomersRequest;
use App\Http\Requests\Customer\CreateCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Requests\Customer\DeleteCustomerRequest;
use App\Http\Requests\Customer\DeleteCustomersRequest;

class CustomerController extends Controller
{
    /**
     * @var CustomerService
     */
    protected $service;

    /**
     * CustomerController constructor.
     *
     * @param CustomerService $service
     */
    public function __construct(CustomerService $service)
    {
        $this->service = $service;
    }

    /**
     * Show customers.
     *
     * @param ShowCustomersRequest $request
     * @return CustomerResources|array
     */
    public function showCustomers(ShowCustomersRequest $request): CustomerResources|array
    {
        return $this->service->showCustomers($request->validated());
    }

    /**
     * Create customer.
     *
     * @param CreateCustomerRequest $request
     * @return array
     */
    public function createCustomer(CreateCustomerRequest $request): array
    {
        return $this->service->createCustomer($request->validated());
    }

    /**
     * Delete multiple customers.
     *
     * @param DeleteCustomersRequest $request
     * @return array
     */
    public function deleteCustomers(DeleteCustomersRequest $request): array
    {
        $customerIds = request()->input('customer_ids', []);
        return $this->service->deleteCustomers($customerIds);
    }

    /**
     * Show single customer.
     *
     * @param ShowCustomerRequest $request
     * @param Customer $customer
     * @return CustomerResource
     */
    public function showCustomer(ShowCustomerRequest $request, Customer $customer): CustomerResource
    {
        return $this->service->showCustomer($customer);
    }

    /**
     * Update customer.
     *
     * @param UpdateCustomerRequest $request
     * @param Customer $customer
     * @return array
     */
    public function updateCustomer(UpdateCustomerRequest $request, Customer $customer): array
    {
        return $this->service->updateCustomer($customer, $request->validated());
    }

    /**
     * Delete customer.
     *
     * @param DeleteCustomerRequest $request
     * @param Customer $customer
     * @return array
     */
    public function deleteCustomer(DeleteCustomerRequest $request, Customer $customer): array
    {
        return $this->service->deleteCustomer($customer);
    }
}
