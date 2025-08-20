<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiTopicResource extends JsonResource
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
            'position' => $this->position,
            'system_prompt' => $this->system_prompt,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),

            // Relationships
            'ai_lessons' => AiLessonResource::collection($this->whenLoaded('aiLessons')),
            'ai_messages' => AiMessageResource::collection($this->whenLoaded('aiMessages')),
        ];
    }
}
