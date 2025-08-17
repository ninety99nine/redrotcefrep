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
use App\Http\Requests\Customer\UpdateCustomersRequest;
use App\Http\Requests\Customer\ImportCustomersRequest;
use \Symfony\Component\HttpFoundation\BinaryFileResponse;

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
     * @return CustomerResources|BinaryFileResponse|array
     */
    public function showCustomers(ShowCustomersRequest $request): CustomerResources|BinaryFileResponse|array
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
     * Update customers.
     *
     * @param UpdateCustomersRequest $request
     * @return array
     */
    public function updateCustomers(UpdateCustomersRequest $request): array
    {
        return $this->service->updateCustomers($request->validated());
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
     * Import customers from CSV.
     *
     * @param ImportCustomersRequest $request
     * @return array
     */
    public function importCustomers(ImportCustomersRequest $request): array
    {
        return $this->service->importCustomers($request->validated());
    }

    /**
     * Show customer.
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
