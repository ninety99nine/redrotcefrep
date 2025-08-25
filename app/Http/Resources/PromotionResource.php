<?php

namespace App\Http\Resources;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'active' => $this->active,
            'user_id' => $this->user_id,
            'store_id' => $this->store_id,
            'currency' => $this->currency,
            'description' => $this->description,
            'hours_of_day' => $this->hours_of_day,
            'offer_discount' => $this->offer_discount,
            'days_of_the_week' => $this->days_of_the_week,
            'days_of_the_month' => $this->days_of_the_month,
            'discount_flat_rate' => $this->discount_flat_rate,
            'months_of_the_year' => $this->months_of_the_year,
            'remaining_quantity' => $this->remaining_quantity,
            'minimum_grand_total' => $this->minimum_grand_total,
            'activate_using_code' => $this->activate_using_code,
            'offer_free_delivery' => $this->offer_free_delivery,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'discount_rate_type' => $this->discount_rate_type,
            'minimum_total_products' => $this->minimum_total_products,
            'discount_percentage_rate' => $this->discount_percentage_rate,
            'activate_for_new_customer' => $this->activate_for_new_customer,
            'activate_using_usage_limit' => $this->activate_using_usage_limit,
            'activate_using_end_datetime' => $this->activate_using_end_datetime,
            'activate_using_hours_of_day' => $this->activate_using_hours_of_day,
            'activate_using_start_datetime' => $this->activate_using_start_datetime,
            'activate_for_existing_customer' => $this->activate_for_existing_customer,
            'activate_using_days_of_the_week' => $this->activate_using_days_of_the_week,
            'activate_using_days_of_the_month' => $this->activate_using_days_of_the_month,
            'minimum_total_product_quantities' => $this->minimum_total_product_quantities,
            'activate_using_months_of_the_year' => $this->activate_using_months_of_the_year,
            'activate_using_minimum_grand_total' => $this->activate_using_minimum_grand_total,
            'end_datetime' => $this->end_datetime ? $this->end_datetime->toDateTimeString() : null,
            'activate_using_minimum_total_products' => $this->activate_using_minimum_total_products,
            'start_datetime' => $this->start_datetime ? $this->start_datetime->toDateTimeString() : null,
            'activate_using_minimum_total_product_quantities' => $this->activate_using_minimum_total_product_quantities,

            'user' => UserResource::make($this->whenLoaded('user')),
            'store' => StoreResource::make($this->whenLoaded('store')),

            '_links' => [
                'show' => route('show.promotion', ['promotion' => $this->id]),
                'update' => route('update.promotion', ['promotion' => $this->id]),
                'delete' => route('delete.promotion', ['promotion' => $this->id]),
            ],
        ];
    }
}
