<?php

namespace App\Http\Resources;

use App\Models\AiAssistant;
use Illuminate\Http\Request;
use App\Services\PhoneNumberService;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'email_verified_at' => $this->email_verified_at ? $this->email_verified_at->toDateTimeString() : null,
            'mobile_number' => $this->mobile_number ? PhoneNumberService::formatPhoneNumber($this->mobile_number) : null,

            'has_password' => !is_null($this->password),
            'has_google'   => !is_null($this->google_id),
            'has_facebook' => !is_null($this->facebook_id),
            'has_linkedin' => !is_null($this->linkedin_id),

            // Optional: count of social logins
            'social_login_count' => ($this->google_id ? 1 : 0) +
                                    ($this->facebook_id ? 1 : 0) +
                                    ($this->linkedin_id ? 1 : 0),

            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'stores' => StoreResource::collection($this->whenLoaded('stores')),
            'ai_assistant' => AiAssistantResource::make($this->whenLoaded('aiAssistant')),
            'ai_messages' => AiMessageResource::collection($this->whenLoaded('aiMessages')),
            'visited_stores' => StoreResource::collection($this->whenLoaded('visitedStores')),
            'followed_stores' => StoreResource::collection($this->whenLoaded('followedStores')),

            //  Pivot
            'store_user' => $this->store_user ? StoreUserResource::make($this->store_user) : null,
        ];
    }
}
