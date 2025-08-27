<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
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
            'id' => $this->id,     //  not available when validating address
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'type' => $this->type,
            'place_id' => $this->place_id,
            'owner_id' => $this->owner_id,
            'owner_type' => $this->owner_type,
            'description' => $this->description,
            'postal_code' => $this->postal_code,
            'address_line' => $this->address_line,
            'complete_address' => $this->complete_address,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'address_line2' => $this->address_line2,
            'created_at' => $this->created_at?->toDateTimeString(),     //  not available when validating address
            'updated_at' => $this->updated_at?->toDateTimeString(),     //  not available when validating address

            'owner' => $this->whenLoaded('owner', fn() => $this->getOwnerResource()),
        ];
    }

    /**
     * Get the appropriate resource for the owner based on owner type.
     *
     * @return StoreResource|CustomerResource|null
     */
    private function getOwnerResource()
    {
        if ($this->owner_type === 'store') {
            return StoreResource::make($this->owner);
        }

        if ($this->owner_type === 'customer') {
            return new CustomerResource($this->owner);
        }

        return null;
    }
}
