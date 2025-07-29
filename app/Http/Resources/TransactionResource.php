<?php

namespace App\Http\Resources;

use App\Models\Store;
use App\Models\PricingPlan;
use App\Models\AiAssistant;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Http\Resources\StoreResource;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\PricingPlanResource;
use App\Http\Resources\PaymentMethodResource;
use App\Http\Resources\AiAssistantResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'amount' => $this->amount,
            'store_id' => $this->store_id,
            'metadata' => $this->metadata,
            'currency' => $this->currency,
            'owner_id' => $this->owner_id,
            'owner_type' => $this->owner_type,
            'percentage' => $this->percentage,
            'description' => $this->description,
            'customer_id' => $this->customer_id,
            'failure_type' => $this->failure_type,
            'failure_reason' => $this->failure_reason,
            'payment_status' => $this->payment_status,
            'ai_assistant_id' => $this->ai_assistant_id,
            'payment_method_id' => $this->payment_method_id,
            'verification_type' => $this->verification_type,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'requested_by_user_id' => $this->requested_by_user_id,
            'created_using_auto_billing' => $this->created_using_auto_billing,
            'manually_verified_by_user_id' => $this->manually_verified_by_user_id,

            'store' => new StoreResource($this->whenLoaded('store')),
            'photo' => new MediaFileResource($this->whenLoaded('photo')),
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'photos' => MediaFileResource::collection($this->whenLoaded('photos')),
            'owner' => $this->whenLoaded('owner', fn() => $this->getOwnerResource()),
            'ai_assistant' => new AiAssistantResource($this->whenLoaded('aiAssistant')),
            'requested_by_user' => new UserResource($this->whenLoaded('requestedByUser')),
            'media_files' => MediaFileResource::collection($this->whenLoaded('mediaFiles')),
            'payment_method' => new PaymentMethodResource($this->whenLoaded('paymentMethod')),
            'manually_verified_by_user' => new UserResource($this->whenLoaded('manuallyVerifiedByUser')),

            '_links' => [
                'show' => route('show.transaction', ['transaction' => $this->id]),
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
        if ($this->owner_type === 'store') {
            return new StoreResource($this->owner);
        }

        if ($this->owner_type === 'pricing plan') {
            return new PricingPlanResource($this->owner);
        }

        return null;
    }
}
