<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StorePaymentMethodResource extends JsonResource
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
            'active' => $this->active,
            'configs' => $this->configs,
            'position' => $this->position,
            'store_id' => $this->store_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'custom_name' => $this->custom_name,
            'instruction' => $this->instruction,
            'payment_method_id' => $this->payment_method_id,
            'requires_verification' => $this->requires_verification,
            'require_proof_of_payment' => $this->require_proof_of_payment,
            'enable_contact_seller_before_payment' => $this->enable_contact_seller_before_payment,
            'mark_as_paid_on_customer_confirmation' => $this->mark_as_paid_on_customer_confirmation,

            'logo' => MediaFileResource::make($this->whenLoaded('logo')),
            'photo' => MediaFileResource::make($this->whenLoaded('photo')),
            'payment_method' => PaymentMethodResource::make($this->whenLoaded('paymentMethod')),
        ];
    }
}
