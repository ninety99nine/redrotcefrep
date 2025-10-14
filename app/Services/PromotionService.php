<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\Store;
use League\Csv\Reader;
use App\Enums\RateType;
use App\Models\Promotion;
use App\Enums\Association;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\PromotionResource;
use App\Http\Resources\PromotionResources;
use App\Http\Requests\Promotion\CreatePromotionRequest;

class PromotionService extends BaseService
{
    /**
     * Show promotions.
     *
     * @param array $data
     * @return PromotionResources|array
     */
    public function showPromotions(array $data): PromotionResources|array
    {
        $storeId = $data['store_id'] ?? null;
        $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;

        if($association == Association::SUPER_ADMIN) {
            $query = Promotion::query();
        }else {
            $query = Promotion::where('store_id', $storeId);
        }

        $query = $query->when(!request()->has('_sort'), fn($query) => $query->latest());
        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create promotion.
     *
     * @param array $data
     * @return array
     */
    public function createPromotion(array $data): array
    {
        $storeId = $data['store_id'];
        $store = Store::findOrFail($storeId);

        $data = array_merge($data, [
            'currency' => $store->currency
        ]);

        $promotion = Promotion::create($data);
        return $this->showCreatedResource($promotion);
    }

    /**
     * Update multiple promotions.
     *
     * @param array $data
     * @return array
     */
    public function updatePromotions(array $data): array
    {
        $storeId = $data['store_id'];
        $promotionsData = $data['promotions'] ?? [];

        $totalPromotions = 0;

        foreach ($promotionsData as $promotionData) {

            $promotion = Promotion::where('id', $promotionData['id'])
                ->where('store_id', $storeId)
                ->first();

            if (!$promotion) {
                continue;
            }

            // Filter fillable fields
            $fillableData = array_intersect_key(
                $promotionData,
                array_flip($promotion->getFillable())
            );

            // Update promotion with fillable data
            $promotion->update($fillableData);

            $totalPromotions++;
        }

        return ['updated' => true, 'message' => $totalPromotions . ($totalPromotions == 1 ? ' promotion': ' promotions') . ' updated'];
    }

    /**
     * Show promotion by code.
     *
     * @param string $code
     * @return PromotionResource
     */
    public function showPromotionByCode(string $code): PromotionResource
    {
        $promotion = Promotion::where('id', $code)->orWhere('code', $code)
                    ->with($this->getRequestRelationships())
                    ->withCount($this->getRequestCountableRelationships())
                    ->firstOrFail();

        return $this->showResource($promotion);
    }

    /**
     * Delete Promotions.
     *
     * @param array $promotionIds
     * @return array
     * @throws Exception
     */
    public function deletePromotions(array $promotionIds): array
    {
        $promotions = Promotion::whereIn('id', $promotionIds)->get();

        if ($totalPromotions = $promotions->count()) {

            foreach ($promotions as $promotion) {

                $this->deletePromotion($promotion);

            }

            return ['message' => $totalPromotions . ($totalPromotions == 1 ? ' Promotion' : ' Promotions') . ' deleted'];

        } else {
            throw new Exception('No Promotions deleted');
        }
    }

    /**
     * Show promotion.
     *
     * @param Promotion $promotion
     * @return PromotionResource
     */
    public function showPromotion(Promotion $promotion): PromotionResource
    {
        return $this->showResource($promotion);
    }

    /**
     * Update promotion.
     *
     * @param Promotion $promotion
     * @param array $data
     * @return array
     */
    public function updatePromotion(Promotion $promotion, array $data): array
    {
        $promotion->update($data);
        return $this->showUpdatedResource($promotion);
    }

    /**
     * Delete promotion.
     *
     * @param Promotion $promotion
     * @return array
     * @throws Exception
     */
    public function deletePromotion(Promotion $promotion): array
    {
        $deleted = $promotion->delete();

        if ($deleted) {
            return ['message' => 'Promotion deleted'];
        } else {
            throw new Exception('Promotion delete unsuccessful');
        }
    }

    /**
     * Import promotions from CSV.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function importPromotions(array $data): array
    {
        $errors = [];
        $file = $data['file'];
        $storeId = $data['store_id'];
        $store = Store::findOrFail($storeId);

        $csv = Reader::createFromPath($file->getPathname(), 'r');
        $csv->setHeaderOffset(0);
        $headers = $csv->getHeader();

        $records = $csv->getRecords();

        DB::beginTransaction();

        try {
            $totalPromotions = 0;
            $promotionsToCreate = [];

            // Collect all CSV rows
            foreach ($records as $index => $record) {
                try {
                    $name = empty($record['Name'] ?? null) ? null : trim($record['Name']);
                    $code = empty($record['Code'] ?? null) ? null : trim($record['Code']);
                    $description = empty($record['Description'] ?? null) ? null : trim($record['Description']);

                    $discountPercentageRate = empty($record['Discount Percentage'] ?? null) ? null : (float) $record['Discount Percentage'];
                    $discountFlatRate = empty($record['Discount Flat Amount'] ?? null) ? null : (float) $record['Discount Flat Amount'];

                    if (!empty($discountPercentageRate) && !empty($discountFlatRate)) {
                        $errors[] = [
                            'row' => $index,
                            'messages' => ['Only one discount type (percentage or flat amount) can be provided.']
                        ];
                        continue;
                    }

                    $offerDiscount = !empty($discountPercentageRate) || !empty($discountFlatRate);
                    $discountRateType = !empty($discountPercentageRate) ? RateType::PERCENTAGE->value : (!empty($discountFlatRate) ? RateType::FLAT->value : null);

                    $offerFreeDelivery = empty($record['Offer Free Delivery'] ?? null) ? 'false' : $record['Offer Free Delivery'];
                    $offerFreeDelivery = filter_var($offerFreeDelivery, FILTER_VALIDATE_BOOLEAN);

                    if (!$offerDiscount && !$offerFreeDelivery) {
                        $errors[] = [
                            'row' => $index,
                            'messages' => ['At least one offer (discount or free delivery) is required.']
                        ];
                        continue;
                    }

                    $minimumSpend = empty($record['Minimum Spend'] ?? null) ? null : (float) $record['Minimum Spend'];

                    // Set to null if empty to avoid validation errors
                    $minimumTotalProducts = empty($record['Minimum Total Products'] ?? null) ? null : (int) $record['Minimum Total Products'];
                    $minimumTotalProductQuantities = empty($record['Minimum Total Product Quantities'] ?? null) ? null : (int) $record['Minimum Total Product Quantities'];

                    $startDate = empty($record['Start Date'] ?? null) ? null : $record['Start Date'];
                    $startDatetime = !is_null($startDate) ? Carbon::parse($startDate)->toDateTimeString() : null;

                    $endDate = empty($record['End Date'] ?? null) ? null : $record['End Date'];
                    $endDatetime = !is_null($endDate) ? Carbon::parse($endDate)->toDateTimeString() : null;

                    // Format hours_of_day to HH:MM
                    $hoursOfDay = empty($record['Hours of Day'] ?? null) ? null : array_map(function ($hour) {
                        $hour = (int) trim($hour);
                        return sprintf('%02d:00', $hour);
                    }, explode(',', $record['Hours of Day']));

                    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                    $daysOfTheWeek = empty($record['Days of the Week'] ?? null) ? null : array_map(function ($day) use ($days) {
                        $day = trim(strtolower($day));
                        return in_array($day, $days) ? ucfirst($day) : null;
                    }, explode(',', $record['Days of the Week']));
                    $daysOfTheWeek = !empty($daysOfTheWeek) ? array_filter($daysOfTheWeek) : null;

                    $daysOfTheMonth = empty($record['Days of the Month'] ?? null) ? null : array_map(function ($day) {
                        $day = (int) trim($day);
                        return $day >= 1 && $day <= 31 ? sprintf('%02d', $day) : null;
                    }, explode(',', $record['Days of the Month']));
                    $daysOfTheMonth = !empty($daysOfTheMonth) ? array_filter($daysOfTheMonth) : null;

                    $months = [
                        'January', 'February', 'March', 'April', 'May', 'June', 'July',
                        'August', 'September', 'October', 'November', 'December'
                    ];
                    $monthsOfTheYear = empty($record['Months of the Year'] ?? null) ? null : array_map(function ($month) use ($months) {
                        $month = trim(strtolower($month));
                        $key = array_search($month, array_map('strtolower', $months));
                        return $key !== false ? $months[$key] : null;
                    }, explode(',', $record['Months of the Year']));
                    $monthsOfTheYear = !empty($monthsOfTheYear) ? array_filter($monthsOfTheYear) : null;

                    $newCustomers = empty($record['New Customers'] ?? null) ? 'false' : $record['New Customers'];
                    $newCustomers = filter_var($newCustomers, FILTER_VALIDATE_BOOLEAN);

                    $existingCustomers = empty($record['Existing Customers'] ?? null) ? 'false' : $record['Existing Customers'];
                    $existingCustomers = filter_var($existingCustomers, FILTER_VALIDATE_BOOLEAN);

                    $usageLimit = empty($record['Usage Limit'] ?? null) ? 0 : (int) $record['Usage Limit'];

                    $active = empty($record['Active'] ?? null) ? 'true' : $record['Active'];
                    $active = filter_var($active, FILTER_VALIDATE_BOOLEAN);

                    $userId = empty($record['User ID'] ?? null) ? null : $record['User ID'];

                    $promotionData = [
                        'name' => $name,
                        'active' => $active,
                        'user_id' => $userId,
                        'store_id' => $storeId,
                        'description' => $description,
                        'currency' => $store->currency,
                        'offer_discount' => $offerDiscount,
                        'offer_free_delivery' => $offerFreeDelivery,
                        'activate_for_new_customer' => $newCustomers,
                        'activate_for_existing_customer' => $existingCustomers,
                    ];

                    if (!is_null($discountRateType)) {
                        $promotionData['discount_rate_type'] = $discountRateType;
                    }
                    if (!is_null($discountFlatRate)) {
                        $promotionData['discount_flat_rate'] = $discountFlatRate;
                    }
                    if (!is_null($discountPercentageRate)) {
                        $promotionData['discount_percentage_rate'] = $discountPercentageRate;
                    }
                    if (!is_null($code)) {
                        $promotionData['activate_using_code'] = true;
                        $promotionData['code'] = $code;
                    }
                    if (!is_null($minimumSpend)) {
                        $promotionData['activate_using_minimum_grand_total'] = true;
                        $promotionData['minimum_grand_total'] = $minimumSpend;
                    }
                    if (!is_null($minimumTotalProducts)) {
                        $promotionData['activate_using_minimum_total_products'] = true;
                        $promotionData['minimum_total_products'] = $minimumTotalProducts;
                    }
                    if (!is_null($minimumTotalProductQuantities)) {
                        $promotionData['activate_using_minimum_total_product_quantities'] = true;
                        $promotionData['minimum_total_product_quantities'] = $minimumTotalProductQuantities;
                    }
                    if (!is_null($startDatetime)) {
                        $promotionData['activate_using_start_datetime'] = true;
                        $promotionData['start_datetime'] = $startDatetime;
                    }
                    if (!is_null($endDatetime)) {
                        $promotionData['activate_using_end_datetime'] = true;
                        $promotionData['end_datetime'] = $endDatetime;
                    }
                    if (!is_null($usageLimit)) {
                        $promotionData['activate_using_usage_limit'] = true;
                        $promotionData['remaining_quantity'] = $usageLimit;
                    }
                    if (!empty($hoursOfDay)) {
                        $promotionData['activate_using_hours_of_day'] = true;
                        $promotionData['hours_of_day'] = $hoursOfDay;
                    }
                    if (!empty($daysOfTheWeek)) {
                        $promotionData['activate_using_days_of_the_week'] = true;
                        $promotionData['days_of_the_week'] = $daysOfTheWeek;
                    }
                    if (!empty($daysOfTheMonth)) {
                        $promotionData['activate_using_days_of_the_month'] = true;
                        $promotionData['days_of_the_month'] = $daysOfTheMonth;
                    }
                    if (!empty($monthsOfTheYear)) {
                        $promotionData['activate_using_months_of_the_year'] = true;
                        $promotionData['months_of_the_year'] = $monthsOfTheYear;
                    }

                    // Validate promotion data
                    $validator = validator($promotionData, (new CreatePromotionRequest)->rules(true), (new CreatePromotionRequest)->messages());

                    if ($validator->fails()) {
                        $errors[] = [
                            'row' => $index,
                            'messages' => $validator->errors()->all()
                        ];
                        continue;
                    }

                    $promotionsToCreate[] = $promotionData;
                    $totalPromotions++;

                } catch (Exception $e) {
                    $errors[] = [
                        'row' => $index,
                        'messages' => [$e->getMessage()]
                    ];
                }
            }

            // Create or update promotions
            try {

                foreach ($promotionsToCreate as $promotionData) {
                    $name = $promotionData['name'];
                    $code = $promotionData['code'] ?? null;

                    $query = Promotion::where('store_id', $storeId)
                        ->where(function ($query) use ($name, $code) {
                            $query->where('name', $name);
                            if (!is_null($code)) {
                                $query->orWhere('code', $code);
                            }
                        });

                    if ($query->count()) {
                        $query->update($promotionData);
                    } else {
                        Promotion::create($promotionData);
                    }
                }
            } catch (Exception $e) {
                throw new Exception('Failed to create promotions: ' . $e->getMessage());
            }

            DB::commit();

            return [
                'message' => $totalPromotions . ($totalPromotions == 1 ? ' promotion' : ' promotions') . ' imported successfully',
                'errors' => $errors
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
}
