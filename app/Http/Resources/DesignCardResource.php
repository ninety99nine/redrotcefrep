<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\StoreResource;
use Illuminate\Http\Resources\Json\JsonResource;

class DesignCardResource extends JsonResource
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
            'type' => $this->type,
            'visible' => $this->visible,
            'position' => $this->position,
            'metadata' => $this->metadata,
            'store_id' => $this->store_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            'store' => StoreResource::make($this->whenLoaded('store')),
            'photo' => MediaFileResource::make($this->whenLoaded('photo')),
            'photos' => MediaFileResource::collection($this->whenLoaded('photos')),
            'media_files' => MediaFileResource::collection($this->whenLoaded('mediaFiles')),
        ];
    }
}
