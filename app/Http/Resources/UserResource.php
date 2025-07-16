<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Services\PhoneNumberService;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'email_verified_at' => $this->email_verified_at ? $this->email_verified_at->toDateTimeString() : null,
            'mobile_number' => $this->mobile_number ? PhoneNumberService::formatPhoneNumber($this->mobile_number) : null,
            '_links' => [
                /*
                'show' => route('show.user', ['user' => $this->id]),
                'update' => route('update.user', ['user' => $this->id]),
                'delete' => route('delete.user', ['user' => $this->id])
                */
            ]
        ];
    }
}
