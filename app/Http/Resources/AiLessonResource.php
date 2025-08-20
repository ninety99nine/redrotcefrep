<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiLessonResource extends JsonResource
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
            'title' => $this->title,
            'prompt' => $this->prompt,
            'visible' => $this->visible,
            'position' => $this->position,
            'ai_topic_id' => $this->ai_topic_id,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),

            // Relationships
            'ai_topic' => new AiTopicResource($this->whenLoaded('aiTopic')),
        ];
    }
}
