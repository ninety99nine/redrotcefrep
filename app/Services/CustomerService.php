<?php

namespace App\Services;

use Exception;
use App\Models\Tag;
use App\Models\Store;
use App\Enums\TagType;
use League\Csv\Reader;
use App\Models\Customer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\CustomerResources;
use App\Http\Requests\Customer\CreateCustomerRequest;
use \Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerService extends BaseService
{
    /**
     * Show customers.
     *
     * @param array $data
     * @return CustomerResources|array
     */
    public function showCustomers(array $data): CustomerResources|BinaryFileResponse|array
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
        $tags = $data['tags'] ?? null;

        $customer = Customer::create($data);

        if(!is_null($tags)) {
            $this->createCustomerTags($customer, $tags);
        }

        return $this->showCreatedResource($customer);
    }



    /**
     * Update multiple customers.
     *
     * @param array $data
     * @return array
     */
    public function updateCustomers(array $data): array
    {
        $storeId = $data['store_id'];
        $globalNotes = $data['notes'] ?? null;
        $customersData = $data['customers'] ?? [];
        $tagsToAdd = $data['tags_to_add'] ?? null;
        $tagsToRemove = $data['tags_to_remove'] ?? null;

        $totalCustomers = 0;

        foreach ($customersData as $customerData) {

            $customer = Customer::where('id', $customerData['id'])
                ->where('store_id', $storeId)
                ->first();

            if (!$customer) {
                continue;
            }

            // Merge global notes setting if provided
            if (!is_null($globalNotes)) {
                $customerData['notes'] = $globalNotes;
            }

            // Filter fillable fields
            $fillableData = array_intersect_key(
                $customerData,
                array_flip($customer->getFillable())
            );

            // Update customer with fillable data
            $customer->update($fillableData);

            $tags = $customerData['tags'] ?? null;

            if(!is_null($tags)) {
                $this->createCustomerTags($customer, $tags);
            }

            // Handle tags to add
            if (!is_null($tagsToAdd)) {
                $customer->tags()->syncWithoutDetaching($tagsToAdd);
            }

            // Handle tags to remove
            if (!is_null($tagsToRemove)) {
                $customer->tags()->detach($tagsToRemove);
            }

            $totalCustomers = $totalCustomers + 1;
        }

        return ['updated' => true, 'message' => $totalCustomers . ($totalCustomers == 1 ? ' customer': ' customers') . ' updated'];
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
     * Import customers from CSV.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function importCustomers(array $data): array
    {
        $errors = [];
        $file = $data['file'];
        $storeId = $data['store_id'];
        $store = Store::findOrFail($storeId);

        $csv = Reader::createFromPath($file->getPathname(), 'r');
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();

        DB::beginTransaction();

        try {

            $totalCustomers = 0;
            $customersToCreate = [];

            // Collect all CSV rows
            foreach ($records as $index => $record) {

                try {

                    $id = empty($record['ID'] ?? null) ? null : $record['ID'];
                    $id = ($id && Str::isUuid($id)) ? $id : Str::uuid()->toString();

                    $email = empty($record['Email'] ?? null) ? null : $record['Email'];
                    $mobileNumber = empty($record['Mobile'] ?? null) ? null : ('+'.ltrim($record['Mobile'], '+'));

                    if (!$email && !$mobileNumber) {
                        $errors[] = [
                            'row' => $index,
                            'messages' => ['Email or mobile number is required']
                        ];
                        continue;
                    }

                    $customerData = [
                        'id' => $id,
                        'email' => $email,
                        'store_id' => $storeId,
                        'currency' => $store->currency,
                        'mobile_number' => $mobileNumber,
                        'notes' => empty($record['Notes'] ?? null) ? null : $record['Notes'],
                        'birthday' => empty($record['Birthday'] ?? null) ? null : $record['Birthday'],
                        'last_name' => empty($record['Last Name'] ?? null) ? null : $record['Last Name'],
                        'first_name' => empty($record['First Name'] ?? null) ? null : $record['First Name'],
                        'referral_code' => empty($record['Referral Code'] ?? null) ? null : $record['Referral Code'],
                        'tags' => empty($record['Tags'] ?? null) ? null : array_map('trim', explode(',', $record['Tags'])),
                    ];

                    // Validate customer data
                    $validator = validator($customerData, (new CreateCustomerRequest)->rules(true), (new CreateCustomerRequest)->messages());

                    if ($validator->fails()) {
                        $errors[] = [
                            'row' => $index,
                            'messages' => $validator->errors()->all()
                        ];
                        continue;
                    }

                    $customersToCreate[] = $customerData;
                    $totalCustomers++;

                } catch (Exception $e) {

                    $errors[] = [
                        'row' => $index,
                        'messages' => [$e->getMessage()]
                    ];

                }
            }

            // Create or update customers
            try {

                foreach ($customersToCreate as $customerData) {

                    $id = $customerData['id'];
                    $email = $customerData['email'];
                    $mobileNumber = $customerData['mobile_number'];

                    $matchingRecords = Customer::where('store_id', $storeId)
                        ->where(function ($query) use ($id, $email, $mobileNumber) {

                            $query->orWhere('id', $id);

                            if (!is_null($email)) {
                                $query->orWhere('email', $email);
                            }

                            if (!is_null($mobileNumber)) {
                                $query->orWhere('mobile_number', $mobileNumber);
                            }

                        })->get();

                    if ($total = $matchingRecords->count()) {

                        // Update the first record
                        $customer = $matchingRecords->first();
                        $customer->update($customerData);

                        if ($total > 1) {

                            // Delete all other duplicates
                            $idsToDelete = $matchingRecords->pluck('id')->slice(1);

                            Customer::whereIn('id', $idsToDelete)->delete();

                        }

                    } else {

                        $customer = Customer::create($customerData);

                    }

                    // Handle tags
                    if (!empty($customerData['tags'])) {
                        $this->createCustomerTags($customer, $customerData['tags']);
                    }
                }

            } catch (Exception $e) {

                throw new Exception('Failed to create customers: ' . $e->getMessage());

            }

            DB::commit();

            return [
                'message' => $totalCustomers . ($totalCustomers == 1 ? ' customer' : ' customers') . ' imported successfully',
                'errors' => $errors
            ];

        } catch (Exception $e) {

            DB::rollBack();
            throw new Exception($e->getMessage());

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
        $tags = $data['tags'] ?? null;
        $addressData = $data['address'] ?? null;

        $customer->update($data);

        if(!is_null($tags)) {
            $this->createCustomerTags($customer, $tags);
        }

        if(!is_null($addressData)) {

            $address = $customer->address()->first();

            if($address) {
                $address->update($addressData);
            }else{
                $customer->address()->create($addressData);
            }
        }

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

    /**
     * Create customer tags.
     *
     * @param Customer $customer
     * @param array<string> $tags - UUIDs or name as string
     */
    private function createCustomerTags(Customer $customer, array $tags) {

        // Process tags
        $tagIds = [];

        if (!empty($tags)) {

            $existingTags = Tag::where('store_id', $customer->store_id)->where('type', TagType::CUSTOMER->value)->get();

            // Match tags by UUID or name
            $matchingUuidTags = $existingTags->filter(fn($existingTag) => collect($tags)->contains($existingTag->id));
            $matchingNameTags = $existingTags->filter(fn($existingTag) => collect($tags)->contains($existingTag->name));

            // Non-matching tags (new tags to create)
            $nonMatchingNameTags = collect($tags)->filter(function ($tag) use ($existingTags) {
                return !Str::isUuid($tag) && !$existingTags->contains('name', $tag);
            })->unique()->values();

            // Create new tags
            foreach ($nonMatchingNameTags as $tagName) {
                $newTag = Tag::create([
                    'name' => $tagName,
                    'type' => TagType::CUSTOMER->value,
                    'store_id' => $customer->store_id
                ]);
                $tagIds[] = $newTag->id;
            }

            // Combine all tag IDs (UUIDs, matching names, new tags)
            $tagIds = array_merge(
                $tagIds,
                $matchingUuidTags->pluck('id')->toArray(),
                $matchingNameTags->pluck('id')->toArray()
            );
        }

        $customer->tags()->sync($tagIds);
    }
}
