<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiMessageResource extends JsonResource
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
            'user_content' => $this->user_content,
            'assistant_content' => $this->assistant_content,
            'prompt_tokens' => $this->prompt_tokens,
            'completion_tokens' => $this->completion_tokens,
            'total_tokens' => $this->total_tokens,
            'request_at' => $this->request_at?->toDateTimeString(),
            'response_at' => $this->response_at?->toDateTimeString(),
            'user_id' => $this->user_id,
            'ai_lesson_id' => $this->ai_lesson_id,
            'ai_assistant_id' => $this->ai_assistant_id,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),

            // Relations
            'user' => UserResource::make($this->whenLoaded('user')),
            'ai_lesson' => AiLessonResource::make($this->whenLoaded('aiLesson')),
            'ai_assistant' => AiAssistantResource::make($this->whenLoaded('aiAssistant')),
        ];
    }
}
