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
            'alias' => $this->alias,
            'email' => $this->email,
            'tax_id' => $this->tax_id,
            'online' => $this->online,
            'country' => $this->country,
            'currency' => $this->currency,
            'language' => $this->language,
            'web_link' => $this->web_link,
            'description' => $this->description,
            'opening_hours' => $this->opening_hours,
            'offer_rewards' => $this->offer_rewards,
            'tax_method' => $this->tax_method,
            'call_to_action' => $this->call_to_action,
            'offline_message' => $this->offline_message,
            'sms_sender_name' => $this->sms_sender_name,
            'weight_unit' => $this->weight_unit,
            'distance_unit' => $this->distance_unit,
            'order_number_padding' => $this->order_number_padding,
            'order_number_counter' => $this->order_number_counter,
            'order_number_prefix' => $this->order_number_prefix,
            'order_number_suffix' => $this->order_number_suffix,
            'qr_code_file_path' => $this->qr_code_file_path,
            'show_opening_hours' => $this->show_opening_hours,
            'message_footer' => $this->message_footer,
            'show_sms_channel' => $this->show_sms_channel,
            'show_line_channel' => $this->show_line_channel,
            'show_whatsapp_channel' => $this->show_whatsapp_channel,
            'show_telegram_channel' => $this->show_telegram_channel,
            'show_messenger_channel' => $this->show_messenger_channel,
            'line_channel_username' => $this->line_channel_username,
            'telegram_channel_username' => $this->telegram_channel_username,
            'messenger_channel_username' => $this->messenger_channel_username,
            'invoice_show_logo' => $this->invoice_show_logo,
            'invoice_show_qr_code' => $this->invoice_show_qr_code,
            'invoice_header' => $this->invoice_header,
            'invoice_footer' => $this->invoice_footer,
            'invoice_company_name' => $this->invoice_company_name,
            'invoice_company_email' => $this->invoice_company_email,
            'tax_percentage_rate' => $this->tax_percentage_rate,
            'reward_percentage_rate' => $this->reward_percentage_rate,
            'allow_checkout_on_closed_hours' => $this->allow_checkout_on_closed_hours,
            'tips' => $this->tips,
            'checkout_fees' => $this->checkout_fees,
            'combine_fees' => $this->combine_fees,
            'combine_discounts' => $this->combine_discounts,
            'seo_title' => $this->seo_title,
            'seo_keywords' => $this->seo_keywords,
            'meta_pixel_id' => $this->meta_pixel_id,
            'tiktok_pixel_id' => $this->tiktok_pixel_id,
            'seo_description' => $this->seo_description,
            'google_analytics_id' => $this->google_analytics_id,

            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'deleted_at' => $this->deleted_at ? $this->deleted_at->toDateTimeString() : null,
            'ussd_mobile_number' => $this->ussd_mobile_number ? PhoneNumberService::formatPhoneNumber($this->ussd_mobile_number) : null,
            'whatsapp_mobile_number' => $this->whatsapp_mobile_number ? PhoneNumberService::formatPhoneNumber($this->whatsapp_mobile_number) : null,
            'invoice_company_mobile_number' => $this->invoice_company_mobile_number ? PhoneNumberService::formatPhoneNumber($this->invoice_company_mobile_number) : null,

            'logo' => MediaFileResource::make($this->whenLoaded('logo')),
            'users' => UserResource::collection($this->whenLoaded('users')),
            'address' => AddressResource::make($this->whenLoaded('address')),
            'seo_image' => MediaFileResource::make($this->whenLoaded('seoImage')),
            'domains' => DomainResources::collection($this->whenLoaded('domains')),
            'product_tags' => TagResource::collection($this->whenLoaded('productTags')),
            'primary_domain' => DomainResource::make($this->whenLoaded('primaryDomain')),
            'customer_tags' => TagResource::collection($this->whenLoaded('customerTags')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'media_files' => MediaFileResource::collection($this->whenLoaded('mediaFiles')),
            'active_subscription' => SubscriptionResource::make($this->whenLoaded('activeSubscription')),

            'orders_count' => $this->whenCounted('orders'),
            'products_count' => $this->whenCounted('products'),
            'customers_count' => $this->whenCounted('customers'),
            'promotions_count' => $this->whenCounted('promotions'),
            'my_following_count' => $this->whenCounted('myFollowing'),
            'my_membership_count' => $this->whenCounted('myMembership'),
            'placed_orders_count' => $this->whenCounted('placedOrders'),
            'created_orders_count' => $this->whenCounted('createdOrders'),
            'assigned_orders_count' => $this->whenCounted('assignedOrders'),
            'active_subscription_count' => $this->whenCounted('activeSubscription'),
        ];
    }
}
