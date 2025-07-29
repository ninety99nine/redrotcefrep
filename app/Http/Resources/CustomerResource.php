<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Services\PhoneNumberService;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'email' => $this->email,
            'notes' => $this->notes,
            'currency' => $this->currency,
            'store_id' => $this->store_id,
            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'total_spend' => $this->total_spend,
            'total_orders' => $this->total_orders,
            'mobile_number' => $this->mobile_number ? PhoneNumberService::formatPhoneNumber($this->mobile_number) : null,
            'total_average_spend' => $this->total_average_spend,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'birthday' => $this->birthday ? $this->birthday->toDateString() : null,
            'last_order_at' => $this->last_order_at ? $this->last_order_at->toDateTimeString() : null,

            'store' => StoreResource::make($this->whenLoaded('store')),

            '_links' => [
                'show' => route('show.customer', ['customer' => $this->id]),
                'update' => route('update.customer', ['customer' => $this->id]),
                'delete' => route('delete.customer', ['customer' => $this->id]),
            ],
        ];
    }
}
