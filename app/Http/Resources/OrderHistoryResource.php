<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderHistoryResource extends JsonResource
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
            'comment' => $this->comment,
            'order_id' => $this->order_id,
            'store_id' => $this->store_id,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            'order' => UserResource::collection($this->whenLoaded('order')),
            'store' => UserResource::collection($this->whenLoaded('store')),
            'user' => UserResource::collection($this->whenLoaded('user')),

            '_links' => [
                //  'show' => route('show.order.history', ['order_history' => $this->id]),
                //  'update' => route('update.order.history', ['order_history' => $this->id]),
                //  'delete' => route('delete.order.history', ['order_history' => $this->id]),
            ],
        ];
    }
}
