<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'active' => $this->active,
            'position' => $this->position,
            'countries' => $this->countries,
            'image_url' => $this->image_url,
            'currencies' => $this->currencies,
            'ussd_codes' => $this->ussd_codes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'config_schema' => $this->config_schema,
            'allowed_countries' => $this->allowed_countries,
            'automated_verification' => $this->automated_verification,
        ];
    }
}
