<?php

namespace App\Services;

use App\Models\Store;
use App\Models\Order;
use App\Enums\FilterResourceType;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;

class FilterService
{
    private $store;

    /**
     * Set store.
     *
     * @param Store $store
     * @return self
     */
    public function setStore(Store $store): self
    {
        $this->store = $store;
        return $this;
    }


    /**
     * Get filters for a specific resource type.
     *
     * @param FilterResourceType $filterResourceType
     * @return array
     */
    public function getFiltersByResourceType(FilterResourceType $filterResourceType): array
    {
        switch ($filterResourceType) {
            case FilterResourceType::ORDERS:
                return self::getOrderFilters();
            case FilterResourceType::REVIEWS:
                return self::getReviewFilters();
            case FilterResourceType::PRODUCTS:
                return self::getProductFilters();
            case FilterResourceType::CUSTOMERS:
                return self::getCustomerFilters();
            case FilterResourceType::PROMOTIONS:
                return self::getPromotionFilters();
            default:
                return [];
        }
    }

    /**
     * Get filters for orders.
     *
     * @return array
     */
    private function getOrderFilters(): array
    {
        $promotions = $this->store ? $this->store->promotions : [];
        //$paymentMethods = $this->store ? $this->store->paymentMethods : [];
        $deliveryMethods = $this->store ? $this->store->deliveryMethods : [];
        $deliveryTimeslots = $this->store ? $this->store->orders()->whereNotNull('delivery_timeslot')->distinct()->pluck('delivery_timeslot')->toArray() : [];

        if(count($deliveryTimeslots)) {

            // Sort the timeslots from earliest to latest
            usort($deliveryTimeslots, function ($a, $b) {

                [$startA] = explode(" - ", $a);
                [$startB] = explode(" - ", $b);

                $timeA = strtotime($startA);
                $timeB = strtotime($startB);

                return $timeA <=> $timeB;

            });

        }

        return collect([
            [
                'label' => 'Status',
                'type' => 'checkboxes',
                'target' => 'status',
                'priority' => true,
                'options' => array_map(fn($status) => [
                    'label' => ucfirst($status),
                    'value' => strtolower($status)
                ], OrderStatus::values())
            ],
            [
                'label' => 'Payment status',
                'target' => 'payment_status',
                'type' => 'checkboxes',
                'priority' => true,
                'options' => array_map(fn($status) => [
                    'label' => ucfirst($status),
                    'value' => strtolower($status)
                ], OrderPaymentStatus::values()),
            ],
            [
                'target' => 'delivery_method_id',
                'label' => 'Delivery methods',
                'type' => 'checkboxes',
                'priority' => true,
                'options' => collect($deliveryMethods)->map(fn($deliveryMethod) => [
                    'label' => ucfirst($deliveryMethod->name),
                    'value' => $deliveryMethod->id
                ])->toArray()
            ],
            [
                'priority' => true,
                'label' => 'Delivery date',
                'target' => 'delivery_date',
                'type' => 'date',
                'options' => self::getNumberOperatorOptions()
            ],
            [
                'target' => 'delivery_timeslot',
                'label' => 'Delivery timeslot',
                'type' => 'checkboxes',
                'priority' => true,
                'options' => collect($deliveryTimeslots)->map(fn($deliveryTimeslot) => [
                    'label' => $deliveryTimeslot,
                    'value' => $deliveryTimeslot
                ])->toArray()
            ],
            [
                'label' => 'Promotions',
                'target' => 'orderPromotions->promotion_id',
                'type' => 'checkboxes',
                'priority' => true,
                'options' => collect($promotions)->map(fn($promotion) => [
                    'label' => ucfirst($promotion->name),
                    'value' => $promotion->id
                ])->toArray()
            ],
            [
                'label' => 'Grand total',
                'target' => 'grand_total',
                'type' => 'money',
                'priority' => true,
                'options' => self::getNumberOperatorOptions()
            ],
            [
                'label' => 'Discount total',
                'target' => 'discount_total',
                'type' => 'money',
                'options' => self::getNumberOperatorOptions()
            ],
            [
                'label' => 'Paid total',
                'target' => 'paid_total',
                'type' => 'money',
                'options' => self::getNumberOperatorOptions()
            ],
            [
                'label' => 'Outstanding total',
                'target' => 'outstanding_total',
                'type' => 'money',
                'options' => self::getNumberOperatorOptions()
            ],
            [
                'label' => 'Total unit products',
                'target' => 'total_products',
                'type' => 'number',
                'options' => self::getNumberOperatorOptions()
            ],
            [
                'label' => 'Total product units',
                'target' => 'total_product_quantities',
                'type' => 'number',
                'options' => self::getNumberOperatorOptions()
            ],
            [
                'priority' => true,
                'label' => 'Created date',
                'target' => 'created_at',
                'type' => 'date',
                'options' => self::getNumberOperatorOptions()
            ],
        ])->filter(fn($filter) => count($filter['options']))->values()->toArray();
    }

    /**
     * Get filters for reviews.
     *
     * @return array
     */
    private function getReviewFilters(): array
    {
        return collect([
            [
                'priority' => true,
                'label' => 'Rating',
                'target' => 'rating',
                'type' => 'money',
                'options' => self::getNumberOperatorOptions()
            ],
            [
                'priority' => true,
                'label' => 'Created date',
                'target' => 'created_at',
                'type' => 'date',
                'options' => self::getNumberOperatorOptions()
            ],
        ])->filter(fn($filter) => count($filter['options']))->values()->toArray();
    }

    /**
     * Get filters for produts.
     *
     * @return array
     */
    private function getProductFilters(): array
    {
        return collect([
            [
                'priority' => true,
                'label' => 'Visibility',
                'type' => 'checkboxes',
                'target' => 'visible',
                'options' => array_map(fn($visibility) => [
                    'label' => $visibility[0],
                    'value' => $visibility[1]
                ], [['Visible', 1], ['Hidden', 0]])
            ],
            [
                'priority' => true,
                'label' => 'Regular Price',
                'target' => 'unit_regular_price',
                'type' => 'money',
                'options' => self::getNumberOperatorOptions()
            ],
            [
                'priority' => true,
                'label' => 'Sale Price',
                'target' => 'unit_sale_price',
                'type' => 'money',
                'options' => self::getNumberOperatorOptions()
            ],
            [
                'priority' => true,
                'label' => 'Created date',
                'target' => 'created_at',
                'type' => 'date',
                'options' => self::getNumberOperatorOptions()
            ],
        ])->filter(fn($filter) => count($filter['options']))->values()->toArray();
    }

    /**
     * Get filters for customers.
     *
     * @return array
     */
    private function getCustomerFilters(): array
    {
        return collect([
            [
                'priority' => true,
                'label' => 'Total Orders',
                'target' => 'total_orders',
                'type' => 'number',
                'options' => self::getNumberOperatorOptions()
            ],
            [
                'priority' => true,
                'label' => 'Total Spend',
                'target' => 'total_spend',
                'type' => 'money',
                'options' => self::getNumberOperatorOptions()
            ],
            [
                'priority' => true,
                'label' => 'Average Spend',
                'target' => 'total_average_spend',
                'type' => 'money',
                'options' => self::getNumberOperatorOptions()
            ],
            [
                'priority' => true,
                'label' => 'Created date',
                'target' => 'created_at',
                'type' => 'date',
                'options' => self::getNumberOperatorOptions()
            ],
        ])->filter(fn($filter) => count($filter['options']))->values()->toArray();
    }

    /**
     * Get filters for promotions.
     *
     * @return array
     */
    private function getPromotionFilters(): array
    {
        return collect([
            [
                'priority' => true,
                'label' => 'Created date',
                'target' => 'created_at',
                'type' => 'date',
                'options' => self::getNumberOperatorOptions()
            ],
        ])->filter(fn($filter) => count($filter['options']))->values()->toArray();
    }

    /**
     * Get string operator options.
     *
     * @return array
     */
    private function getStringOperatorOptions(): array
    {
        return [
            ['label' => 'Equal to', 'operator' => 'eq'],
            ['label' => 'Not equal to', 'operator' => 'neq']
        ];
    }

    /**
     * Get number operator options.
     *
     * @return array
     */
    private function getNumberOperatorOptions(): array
    {
        return [
            ['label' => 'Greater or Equal to', 'operator' => 'gte'],
            ['label' => 'Less or Equal to', 'operator' => 'lte'],
            ['label' => 'Greater than', 'operator' => 'gt'],
            ['label' => 'Less than', 'operator' => 'lt'],
            ['label' => 'Equal to', 'operator' => 'eq'],
            ['label' => 'Not equal to', 'operator' => 'neq'],
            ['label' => 'Between (Including)', 'operator' => 'bt'],
            ['label' => 'Between (Excluding)', 'operator' => 'bt_ex'],
        ];
    }
}
