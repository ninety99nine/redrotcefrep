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
            'state' => $this->state,
            'country' => $this->country,
            'type' => $this->type,
            'place_id' => $this->place_id,
            'order_id' => $this->order_id,
            'description' => $this->description,
            'postal_code' => $this->postal_code,
            'address_line' => $this->address_line,
            'complete_address' => $this->complete_address,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'address_line2' => $this->address_line2,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            'order' => new OrderResource($this->whenLoaded('order')),

            '_links' => [
                'show' => route('show.delivery.address', ['deliveryAddress' => $this->id]),
                'update' => route('update.delivery.address', ['deliveryAddress' => $this->id]),
                'delete' => route('delete.delivery.address', ['deliveryAddress' => $this->id]),
            ],
        ];
    }
}
