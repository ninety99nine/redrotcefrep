<?php

namespace App\Http\Resources;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiAssistantResource extends JsonResource
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
            'total_paid_tokens' => $this->total_paid_tokens,
            'requires_subscription' => $this->requires_subscription,
            'remaining_free_tokens' => $this->remaining_free_tokens,
            'remaining_paid_tokens' => $this->remaining_paid_tokens,
            'remaining_paid_top_up_tokens' => $this->remaining_paid_top_up_tokens,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            'user' => UserResource::collection($this->whenLoaded('user')),
            'transactions' => TransactionResource::collection($this->whenLoaded('transactions')),
            'ai_assistant_token_usage' => AiAssistantTokenUsageResource::make($this->whenLoaded('aiAssistantTokenUsage')),

            '_links' => [
                'show' => route('show.ai.assistant', ['aiAssistant' => $this->id]),
                'update' => route('update.ai.assistant', ['aiAssistant' => $this->id]),
                'delete' => route('delete.ai.assistant', ['aiAssistant' => $this->id]),
            ],
        ];
    }
}
