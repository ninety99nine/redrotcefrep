<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Services\PhoneNumberService;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'rating' => $this->rating,
            'visible' => $this->visible,
            'comment' => $this->comment,
            'user_id' => $this->user_id,
            'store_id' => $this->store_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'mobile_number' => $this->mobile_number ? PhoneNumberService::formatPhoneNumber($this->mobile_number) : null,

            'user' => UserResource::make($this->whenLoaded('user')),
            'store' => StoreResource::make($this->whenLoaded('store')),
        ];
    }
}
