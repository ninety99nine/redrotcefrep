<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\StoreResource;
use Illuminate\Http\Resources\Json\JsonResource;

class DomainResource extends JsonResource
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
            'type' => $this->type,
            'status' => $this->status,
            'verified_at' => $this->verified_at?->toDateTimeString(),
            'store_id' => $this->store_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            'store' => StoreResource::make($this->whenLoaded('store')),
        ];
    }
}
