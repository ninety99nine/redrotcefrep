<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryAddressResource extends JsonResource
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
            'city' => $this->city,
            'type' => $this->type,
            'state' => $this->state,
            'country' => $this->country,
            'place_id' => $this->place_id,
            'order_id' => $this->order_id,
            'description' => $this->description,
            'postal_code' => $this->postal_code,
            'address_line' => $this->address_line,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'address_line2' => $this->address_line2,
            'complete_address' => $this->complete_address,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            'order' => new OrderResource($this->whenLoaded('order'))
        ];
    }
}
