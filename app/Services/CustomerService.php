<?php

namespace App\Services;

use Exception;
use App\Models\Customer;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\CustomerResources;

class CustomerService extends BaseService
{
    /**
     * Show customers.
     *
     * @param array $data
     * @return CustomerResources|array
     */
    public function showCustomers(array $data): CustomerResources|array
    {
        $query = Customer::query()->when(!request()->has('_sort'), fn($query) => $query->latest());
        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create customer.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createCustomer(array $data): array
    {
        $customer = Customer::create($data);
        return $this->showCreatedResource($customer);
    }

    /**
     * Delete customers.
     *
     * @param array $customerIds
     * @return array
     * @throws Exception
     */
    public function deleteCustomers(array $customerIds): array
    {
        $customers = Customer::whereIn('id', $customerIds)->get();

        if ($totalCustomers = $customers->count()) {

            foreach ($customers as $customer) {

                $this->deleteCustomer($customer);

            }

            return ['message' => $totalCustomers . ($totalCustomers == 1 ? ' Customer' : ' Customers') . ' deleted'];
        } else {
            throw new Exception('No Customers deleted');
        }
    }

    /**
     * Show customer.
     *
     * @param Customer $customer
     * @return CustomerResource
     */
    public function showCustomer(Customer $customer): CustomerResource
    {
        return $this->showResource($customer);
    }

    /**
     * Update customer.
     *
     * @param Customer $customer
     * @param array $data
     * @return array
     */
    public function updateCustomer(Customer $customer, array $data): array
    {
        $customer->update($data);
        return $this->showUpdatedResource($customer);
    }

    /**
     * Delete customer.
     *
     * @param Customer $customer
     * @return array
     * @throws Exception
     */
    public function deleteCustomer(Customer $customer): array
    {
        $deleted = $customer->delete();

        if ($deleted) {
            return ['message' => 'Customer deleted'];
        } else {
            throw new Exception('Customer delete unsuccessful');
        }
    }
}
