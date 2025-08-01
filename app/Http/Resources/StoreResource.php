<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Services\PhoneNumberService;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
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
            'tips' => $this->tips,
            'alias' => $this->alias,
            'email' => $this->email,
            'tax_id' => $this->tax_id,
            'online' => $this->online,
            'country' => $this->country,
            'web_link' => $this->web_link,
            'currency' => $this->currency,
            'language' => $this->language,
            'show_tips' => $this->show_tips,
            'show_items' => $this->show_items,
            'description' => $this->description,
            'opening_hours' => $this->opening_hours,
            'checkout_fees' => $this->checkout_fees,
            'offer_rewards' => $this->offer_rewards,
            'tax_method' => $this->tax_method,
            'call_to_action' => $this->call_to_action,
            'offline_message' => $this->offline_message,
            'sms_sender_name' => $this->sms_sender_name,
            'weight_unit' => $this->weight_unit,
            'show_promotions' => $this->show_promotions,
            'show_specify_tip' => $this->show_specify_tip,
            'distance_unit' => $this->distance_unit,
            'qr_code_file_path' => $this->qr_code_file_path,
            'show_opening_hours' => $this->show_opening_hours,
            'social_media_links' => $this->social_media_links,
            'show_customer_email' => $this->show_customer_email,
            'tip_section_heading' => $this->tip_section_heading,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'items_section_heading' => $this->items_section_heading,
            'show_delivery_methods' => $this->show_delivery_methods,
            'delivery_address_title' => $this->delivery_address_title,
            'customer_email_required' => $this->customer_email_required,
            'tax_percentage_rate' => $this->tax_percentage_rate,
            'show_customer_last_name' => $this->show_customer_last_name,
            'delivery_schedule_title' => $this->delivery_schedule_title,
            'show_customer_first_name' => $this->show_customer_first_name,
            'customer_section_heading' => $this->customer_section_heading,
            'promotions_section_heading' => $this->promotions_section_heading,
            'reward_percentage_rate' => $this->reward_percentage_rate,
            'customer_last_name_required' => $this->customer_last_name_required,
            'customer_first_name_required' => $this->customer_first_name_required,
            'combine_fees_into_one_amount' => $this->combine_fees_into_one_amount,
            'cost_breakdown_section_heading' => $this->cost_breakdown_section_heading,
            'allow_checkout_on_closed_hours' => $this->allow_checkout_on_closed_hours,
            'delivery_methods_section_heading' => $this->delivery_methods_section_heading,
            'combine_discounts_into_one_amount' => $this->combine_discounts_into_one_amount,
            'deleted_at' => $this->deleted_at ? $this->deleted_at->toDateTimeString() : null,
            'ussd_mobile_number' => $this->ussd_mobile_number ? PhoneNumberService::formatPhoneNumber($this->ussd_mobile_number) : null,
            'contact_mobile_number' => $this->contact_mobile_number ? PhoneNumberService::formatPhoneNumber($this->contact_mobile_number) : null,
            'whatsapp_mobile_number' => $this->whatsapp_mobile_number ? PhoneNumberService::formatPhoneNumber($this->whatsapp_mobile_number) : null,

            'logo' => new MediaFileResource($this->whenLoaded('logo')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'users' => UserResource::collection($this->whenLoaded('users')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'media_files' => MediaFileResource::collection($this->whenLoaded('mediaFiles')),
            'active_subscription' => new SubscriptionResource($this->whenLoaded('activeSubscription')),

            '_links' => [
                'show' => route('show.store', ['store' => $this->id]),
                'update' => route('update.store', ['store' => $this->id]),
                'delete' => route('delete.store', ['store' => $this->id])
            ],
        ];
    }
}
