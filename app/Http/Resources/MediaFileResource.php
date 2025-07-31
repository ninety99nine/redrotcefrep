<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MediaFileResource extends JsonResource
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
            'name' => $this->name,
            'path' => $this->path,
            'size' => $this->size,
            'type' => $this->type,
            'width' => $this->width,
            'height' => $this->height,
            'mime_type' => $this->mime_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            '_links' => [
                'show' => route('show.media.file', ['mediaFile' => $this->id]),
                'update' => route('update.media.file', ['mediaFile' => $this->id]),
                'delete' => route('delete.media.file', ['mediaFile' => $this->id]),
            ],
        ];
    }
}
