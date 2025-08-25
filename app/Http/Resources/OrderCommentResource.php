<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderCommentResource extends JsonResource
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

            'user' => UserResource::make($this->whenLoaded('user')),
            'store' => StoreResource::make($this->whenLoaded('store')),
            'order' => OrderResource::make($this->whenLoaded('order')),
            'photo' => MediaFileResource::make($this->whenLoaded('photo')),
            'photos' => MediaFileResource::collection($this->whenLoaded('photos')),
            'media_files' => MediaFileResource::collection($this->whenLoaded('mediaFiles')),
        ];
    }
}
