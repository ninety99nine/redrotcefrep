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

            'user' => new UserResource($this->whenLoaded('user')),
            'store' => new StoreResource($this->whenLoaded('store')),
            'order' => new OrderResource($this->whenLoaded('order')),
            'photo' => new MediaFileResource($this->whenLoaded('photo')),
            'photos' => MediaFileResource::collection($this->whenLoaded('photos')),
            'media_files' => MediaFileResource::collection($this->whenLoaded('mediaFiles')),

            '_links' => [
                //  'show' => route('show.order_comment', ['order_comment' => $this->id]),
                //  'update' => route('update.order_comment', ['order_comment' => $this->id]),
                //  'delete' => route('delete.order_comment', ['order_comment' => $this->id]),
            ],
        ];
    }
}
