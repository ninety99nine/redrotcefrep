<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AutoBillingScheduleResource extends JsonResource
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
            'attempt' => $this->attempt,
            'user_id' => $this->user_id,
            'store_id' => $this->store_id,
            'pricing_plan_id' => $this->pricing_plan_id,
            'overall_attempts' => $this->overall_attempts,
            'payment_method_id' => $this->payment_method_id,
            'overall_failed_attempts' => $this->overall_failed_attempts,
            'next_attempt_date' => $this->next_attempt_date?->toDateTimeString(),
            'overall_successful_attempts' => $this->overall_successful_attempts,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            // Relationships
            'user' => UserResource::make($this->whenLoaded('user')),
            'store' => StoreResource::make($this->whenLoaded('store')),
            'pricing_plan' => PricingPlanResource::make($this->whenLoaded('pricingPlan')),
            'payment_method' => PaymentMethodResource::make($this->whenLoaded('paymentMethod')),
        ];
    }
}
