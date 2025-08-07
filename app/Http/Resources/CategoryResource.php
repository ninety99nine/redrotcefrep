<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'visible' => $this->visible,
            'store_id' => $this->store_id,
            'description' => $this->description,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            'store' => new StoreResource($this->whenLoaded('store')),
            'photo' => new MediaFileResource($this->whenLoaded('photo')),
            'photos' => MediaFileResource::collection($this->whenLoaded('photos')),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'media_files' => MediaFileResource::collection($this->whenLoaded('mediaFiles')),

            'products_count' => $this->whenCounted('products'),
        ];
    }
}
