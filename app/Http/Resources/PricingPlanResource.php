<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingPlanResource extends JsonResource
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
            'active' => $this->active,
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'billing_type' => $this->billing_type,
            'currency' => $this->currency,
            'price' => $this->price,
            'discount_percentage_rate' => $this->discount_percentage_rate,
            'max_auto_billing_attempts' => $this->max_auto_billing_attempts,
            'auto_billing_disabled_sms_message' => $this->auto_billing_disabled_sms_message,
            'supports_web' => $this->supports_web,
            'supports_ussd' => $this->supports_ussd,
            'supports_mobile' => $this->supports_mobile,
            'metadata' => $this->metadata,
            'features' => $this->features,
            'position' => $this->position,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
