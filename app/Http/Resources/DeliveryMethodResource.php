<?php

namespace App\Http\Resources;

use App\Http\Resources\StoreResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryMethodResource extends JsonResource
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
            'active' => $this->active,
            'currency' => $this->currency,
            'store_id' => $this->store_id,
            'position' => $this->position,
            'charge_fee' => $this->charge_fee,
            'fee_type' => $this->fee_type,
            'description' => $this->description,
            'weight_categories' => $this->weight_categories,
            'distance_zones' => $this->distance_zones,
            'postal_code_zones' => $this->postal_code_zones,
            'set_schedule' => $this->set_schedule,
            'same_day_delivery' => $this->same_day_delivery,
            'operational_hours' => $this->operational_hours,
            'fallback_fee_type' => $this->fallback_fee_type,
            'schedule_type' => $this->schedule_type,
            'time_slot_interval_unit' => $this->time_slot_interval_unit,
            'earliest_delivery_time_unit' => $this->earliest_delivery_time_unit,
            'flat_fee_rate' => $this->flat_fee_rate,
            'percentage_fee_rate' => $this->percentage_fee_rate,
            'minimum_grand_total' => $this->minimum_grand_total,
            'fallback_flat_fee_rate' => $this->fallback_flat_fee_rate,
            'time_slot_interval_value' => $this->time_slot_interval_value,
            'daily_order_limit' => $this->daily_order_limit,
            'set_daily_order_limit' => $this->set_daily_order_limit,
            'ask_for_an_address' => $this->ask_for_an_address,
            'pin_location_on_map' => $this->pin_location_on_map,
            'auto_generate_time_slots' => $this->auto_generate_time_slots,
            'show_distance_on_invoice' => $this->show_distance_on_invoice,
            'fallback_percentage_fee_rate' => $this->fallback_percentage_fee_rate,
            'earliest_delivery_time_value' => $this->earliest_delivery_time_value,
            'latest_delivery_time_value' => $this->latest_delivery_time_value,
            'capture_additional_fields' => $this->capture_additional_fields,
            'additional_fields' => $this->additional_fields,
            'qualify_on_minimum_grand_total' => $this->qualify_on_minimum_grand_total,
            'free_delivery_minimum_grand_total' => $this->free_delivery_minimum_grand_total,
            'offer_free_delivery_on_minimum_grand_total' => $this->offer_free_delivery_on_minimum_grand_total,
            'require_minimum_notice_for_orders' => $this->require_minimum_notice_for_orders,
            'restrict_maximum_notice_for_orders' => $this->restrict_maximum_notice_for_orders,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            'store' => new StoreResource($this->whenLoaded('store')),
            //  'address' => new AddressResource($this->whenLoaded('address')),
            'products' => ProductResource::collection($this->whenLoaded('products')),

            '_links' => [
                'show' => route('show.delivery.method', ['deliveryMethod' => $this->id]),
                'update' => route('update.delivery.method', ['deliveryMethod' => $this->id]),
                'delete' => route('delete.delivery.method', ['deliveryMethod' => $this->id]),
            ],
        ];
    }
}
