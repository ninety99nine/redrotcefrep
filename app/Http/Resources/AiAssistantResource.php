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
            'user_id' => $this->user_id,
            'total_paid_tokens' => $this->total_paid_tokens,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'requires_subscription' => $this->requires_subscription,
            'remaining_free_tokens' => $this->remaining_free_tokens,
            'remaining_paid_tokens' => $this->remaining_paid_tokens,
            'remaining_paid_top_up_tokens' => $this->remaining_paid_top_up_tokens,

            'user' => UserResource::collection($this->whenLoaded('user')),
            'transactions' => TransactionResource::collection($this->whenLoaded('transactions')),
            'subscriptions' => SubscriptionResources::collection($this->whenLoaded('subscriptions')),
            'active_subscription' => SubscriptionResource::make($this->whenLoaded('activeSubscription')),
            'ai_assistant_token_usage' => AiAssistantTokenUsageResource::make($this->whenLoaded('aiAssistantTokenUsage')),

            'user' => $this->whenCounted('user'),
            'transactions' => $this->whenCounted('transactions'),
            'subscriptions' => $this->whenCounted('subscriptions'),
            'active_subscription_count' => $this->whenCounted('activeSubscription'),
            'ai_assistant_token_usage_count' => $this->whenCounted('aiAssistantTokenUsage')
        ];
    }
}
