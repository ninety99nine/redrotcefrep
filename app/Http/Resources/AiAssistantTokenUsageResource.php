<?php

namespace App\Http\Resources;

use App\Http\Resources\AiAssistantResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiAssistantTokenUsageResource extends JsonResource
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
            'ai_assistant_id' => $this->ai_assistant_id,
            'free_tokens_used' => $this->free_tokens_used,
            'paid_tokens_used' => $this->paid_tokens_used,
            'request_tokens_used' => $this->request_tokens_used,
            'response_tokens_used' => $this->response_tokens_used,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'paid_top_up_tokens_used' => $this->paid_top_up_tokens_used,

            'ai_assistant' => AiAssistantResource::make($this->whenLoaded('aiAssistant')),

            '_links' => [
                'show' => route('show.ai.assistant.token.usage', ['aiAssistantTokenUsage' => $this->id]),
                'update' => route('update.ai.assistant.token.usage', ['aiAssistantTokenUsage' => $this->id]),
                'delete' => route('delete.ai.assistant.token.usage', ['aiAssistantTokenUsage' => $this->id]),
            ],
        ];
    }
}
