<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Services\PhoneNumberService;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreUserResource extends JsonResource
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
            'email' => $this->email,
            'creator' => $this->creator,
            'user_id' => $this->user_id,
            'role_id' => $this->role_id,
            'store_id' => $this->store_id,
            'first_name' => $this->first_name,
            'joined_at' => $this->joined_at?->toDateTimeString(),
            'invited_at' => $this->invited_at?->toDateTimeString(),
            'mobile_number' => $this->mobile_number ? PhoneNumberService::formatPhoneNumber($this->mobile_number) : null,

            'user' => UserResource::make($this->whenLoaded('user')),
            'role' => RoleResource::make($this->whenLoaded('role')),
            'store' => StoreResource::make($this->whenLoaded('store')),
        ];
    }
}
