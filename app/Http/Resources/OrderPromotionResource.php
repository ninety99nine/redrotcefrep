<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderPromotionResource extends JsonResource
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
            'order_id' => $this->order_id,
            'store_id' => $this->store_id,
            'promotion_id' => $this->promotion_id,
            'currency' => $this->currency,
            'description' => $this->description,
            'is_cancelled' => $this->is_cancelled,
            'offer_discount' => $this->offer_discount,
            'hours_of_day' => $this->hours_of_day,
            'days_of_the_week' => $this->days_of_the_week,
            'days_of_the_month' => $this->days_of_the_month,
            'months_of_the_year' => $this->months_of_the_year,
            'discount_flat_rate' => $this->discount_flat_rate,
            'remaining_quantity' => $this->remaining_quantity,
            'minimum_grand_total' => $this->minimum_grand_total,
            'detected_changes' => $this->detected_changes,
            'cancellation_reasons' => $this->cancellation_reasons,
            'offer_free_delivery' => $this->offer_free_delivery,
            'activate_using_code' => $this->activate_using_code,
            'discount_percentage_rate' => $this->discount_percentage_rate,
            'minimum_total_products' => $this->minimum_total_products,
            'activate_for_new_customer' => $this->activate_for_new_customer,
            'activate_using_usage_limit' => $this->activate_using_usage_limit,
            'activate_using_end_datetime' => $this->activate_using_end_datetime,
            'activate_using_hours_of_day' => $this->activate_using_hours_of_day,
            'activate_using_start_datetime' => $this->activate_using_start_datetime,
            'activate_for_existing_customer' => $this->activate_for_existing_customer,
            'activate_using_days_of_the_week' => $this->activate_using_days_of_the_week,
            'activate_using_days_of_the_month' => $this->activate_using_days_of_the_month,
            'activate_using_months_of_the_year' => $this->activate_using_months_of_the_year,
            'minimum_total_product_quantities' => $this->minimum_total_product_quantities,
            'activate_using_minimum_grand_total' => $this->activate_using_minimum_grand_total,
            'activate_using_minimum_total_products' => $this->activate_using_minimum_total_products,
            'activate_using_minimum_total_product_quantities' => $this->activate_using_minimum_total_product_quantities,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'end_datetime' => $this->end_datetime ? $this->end_datetime->toDateTimeString() : null,
            'start_datetime' => $this->start_datetime ? $this->start_datetime->toDateTimeString() : null,

            'order' => UserResource::collection($this->whenLoaded('order')),
            'store' => UserResource::collection($this->whenLoaded('store')),
            'promotion' => UserResource::collection($this->whenLoaded('promotion')),

            '_links' => [
                'show' => route('show.order_promotion', ['order_promotion' => $this->id]),
                'update' => route('update.order_promotion', ['order_promotion' => $this->id]),
                'delete' => route('delete.order_promotion', ['order_promotion' => $this->id]),
            ],
        ];
    }
}
