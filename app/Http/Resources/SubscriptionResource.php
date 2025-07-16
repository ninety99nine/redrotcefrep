<?php

namespace App\Http\Resources;

use App\Models\Store;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Http\Resources\StoreResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
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
            'owner_id' => $this->owner_id,
            'owner_type' => $this->owner_type,
            'transaction_id' => $this->transaction_id,
            'pricing_plan_id' => $this->pricing_plan_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'end_at' => $this->end_at ? $this->end_at->toDateTimeString() : null,
            'start_at' => $this->start_at ? $this->start_at->toDateTimeString() : null,

            'user' => new UserResource($this->whenLoaded('user')),
            'owner' => $this->whenLoaded('owner', fn() => $this->getOwnerResource()),
            'pricing_plan' => new PricingPlanResource($this->whenLoaded('pricingPlan')),

            '_links' => [
                'show' => route('show.subscription', ['subscription' => $this->id]),
                'update' => route('update.subscription', ['subscription' => $this->id]),
                'delete' => route('delete.subscription', ['subscription' => $this->id]),
            ],
        ];
    }

    /**
     * Get the appropriate resource for the owner based on owner type.
     *
     * @return PricingPlanResource|StoreResource|null
     */
    private function getOwnerResource()
    {
        if ($this->owner_type === 'user') {
            return new UserResource($this->owner);
        }

        if ($this->owner_type === 'store') {
            return new StoreResource($this->owner);
        }

        return null;
    }
}
