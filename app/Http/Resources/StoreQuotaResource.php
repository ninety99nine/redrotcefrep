<?php

namespace App\Http\Resources;

use App\Http\Resources\StoreResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreQuotaResource extends JsonResource
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
            'store_id' => $this->store_id,
            'sms_credits' => $this->sms_credits,
            'email_credits' => $this->email_credits,
            'whatsapp_credits' => $this->whatsapp_credits,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'sms_credits_expire_at' => $this->sms_credits_expire_at ? $this->sms_credits_expire_at->toDateTimeString() : null,
            'email_credits_expire_at' => $this->email_credits_expire_at ? $this->email_credits_expire_at->toDateTimeString() : null,
            'whatsapp_credits_expire_at' => $this->whatsapp_credits_expire_at ? $this->whatsapp_credits_expire_at->toDateTimeString() : null,

            'store' => StoreResource::collection($this->whenLoaded('store')),

            '_links' => [
                'show' => route('show.store.quota', ['storeQuota' => $this->id]),
                'update' => route('update.store.quota', ['storeQuota' => $this->id]),
                'delete' => route('delete.store.quota', ['storeQuota' => $this->id]),
            ],
        ];
    }
}
