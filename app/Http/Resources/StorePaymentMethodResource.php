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

            'logo' => new MediaFileResource($this->whenLoaded('logo')),
            'photo' => new MediaFileResource($this->whenLoaded('photo')),
            'payment_method' => new PaymentMethodResource($this->whenLoaded('paymentMethod')),

            '_links' => [
                'show' => route('show.store.payment.method', ['storePaymentMethod' => $this->id]),
                'update' => route('update.store.payment.method', ['storePaymentMethod' => $this->id]),
                'delete' => route('delete.store.payment.method', ['storePaymentMethod' => $this->id]),
            ],
        ];
    }
}
