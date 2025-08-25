<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDiscountResource extends JsonResource
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
            'currency' => $this->currency,
            'amount' => $this->amount,
            'order_id' => $this->order_id,
            'store_id' => $this->store_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            'order' => UserResource::collection($this->whenLoaded('order')),
            'store' => StoreResource::make($this->whenLoaded('store')),

            '_links' => [
                //  'show' => route('show.order.discount', ['order_discount' => $this->id]),
                //  'update' => route('update.order.discount', ['order_discount' => $this->id]),
                //  'delete' => route('delete.order.discount', ['order_discount' => $this->id]),
            ],
        ];
    }
}
