<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderFeeResource extends JsonResource
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
            'rate_type' => $this->rate_type,
            'amount' => $this->amount,
            'percentage_rate' => $this->percentage_rate,
            'currency' => $this->currency,
            'order_id' => $this->order_id,
            'store_id' => $this->store_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            'order' => UserResource::collection($this->whenLoaded('order')),
            'store' => UserResource::collection($this->whenLoaded('store')),

            '_links' => [
                //  'show' => route('show.order_fee', ['order_fee' => $this->id]),
                //  'update' => route('update.order_fee', ['order_fee' => $this->id]),
                //  'delete' => route('delete.order_fee', ['order_fee' => $this->id]),
            ],
        ];
    }
}
