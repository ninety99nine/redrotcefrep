<?php

namespace App\Http\Requests\Store;

use App\Enums\TaxMethod;
use App\Enums\WeightUnit;
use App\Enums\DistanceUnit;
use Illuminate\Support\Arr;
use App\Enums\DaysOfTheWeek;
use App\Enums\SocialMediaLink;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('store'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'alias' => ['sometimes', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'ussd_mobile_number' => ['nullable', 'phone:INTERNATIONAL'],
            'contact_mobile_number' => ['nullable', 'phone:INTERNATIONAL'],
            'whatsapp_mobile_number' => ['nullable', 'phone:INTERNATIONAL'],
            'call_to_action' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:120'],
            'qr_code_file_path' => ['nullable', 'string', 'max:255'],
            'offer_rewards' => ['nullable', 'boolean'],
            'reward_percentage_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'social_media_links' => ['nullable', 'array'],
            'social_media_links.*.active' => ['required_with:social_media_links', 'boolean'],
            'social_media_links.*.name' => ['required_with:social_media_links', Rule::enum(SocialMediaLink::class)],
            'social_media_links.*.url' => ['required_with:social_media_links', 'url', 'max:255'],
            'country' => ['nullable', 'string', 'size:2'],
            'currency' => ['nullable', 'string', 'size:3'],
            'language' => ['nullable', 'string', 'size:2'],
            'distance_unit' => ['nullable', Rule::enum(DistanceUnit::class)],
            'weight_unit' => ['nullable', Rule::enum(WeightUnit::class)],
            'tax_method' => ['nullable', Rule::enum(TaxMethod::class)],
            'tax_percentage_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'show_opening_hours' => ['nullable', 'boolean'],
            'allow_checkout_on_closed_hours' => ['nullable', 'boolean'],
            'opening_hours' => ['nullable', 'array'],
            'opening_hours.*.day' => ['required_with:opening_hours', 'string', Rule::enum(DaysOfTheWeek::class)],
            'opening_hours.*.open' => ['required_with:opening_hours', 'string', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'opening_hours.*.close' => ['required_with:opening_hours', 'string', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'online' => ['nullable', 'boolean'],
            'offline_message' => ['nullable', 'string', 'max:120'],
            'sms_sender_name' => ['nullable', 'string', 'max:11'],
            'customer_section_heading' => ['nullable', 'string', 'max:25'],
            'show_customer_email' => ['nullable', 'boolean'],
            'show_customer_last_name' => ['nullable', 'boolean'],
            'show_customer_first_name' => ['nullable', 'boolean'],
            'customer_email_required' => ['nullable', 'boolean'],
            'customer_last_name_required' => ['nullable', 'boolean'],
            'customer_first_name_required' => ['nullable', 'boolean'],
            'show_items' => ['nullable', 'boolean'],
            'items_section_heading' => ['nullable', 'string', 'max:25'],
            'show_delivery_methods' => ['nullable', 'boolean'],
            'delivery_methods_section_heading' => ['nullable', 'string', 'max:25'],
            'delivery_schedule_title' => ['nullable', 'string', 'max:25'],
            'delivery_address_title' => ['nullable', 'string', 'max:25'],
            'show_tips' => ['nullable', 'boolean'],
            'tip_section_heading' => ['nullable', 'string', 'max:25'],
            'tips' => ['nullable', 'array'],
            'tips.*' => ['numeric', 'min:0'],
            'show_specify_tip' => ['nullable', 'boolean'],
            'show_promotions' => ['nullable', 'boolean'],
            'promotions_section_heading' => ['nullable', 'string', 'max:25'],
            'cost_breakdown_section_heading' => ['nullable', 'string', 'max:25'],
            'combine_fees_into_one_amount' => ['nullable', 'boolean'],
            'combine_discounts_into_one_amount' => ['nullable', 'boolean'],
            'checkout_fees' => ['nullable', 'array'],
            'checkout_fees.*.name' => ['required_with:checkout_fees', 'string', 'max:255'],
            'checkout_fees.*.flat_rate' => ['required_with:checkout_fees', 'numeric', 'min:0'],
            'checkout_fees.*.percentage_rate' => ['required_with:checkout_fees', 'numeric', 'min:0', 'max:100'],
            'order_number_padding' => ['nullable', 'integer', 'min:0', 'max:5'],
            'order_number_counter' => ['nullable', 'integer', 'min:0'],
            'order_number_prefix' => ['nullable', 'string', 'max:255'],
            'order_number_suffix' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.string' => 'The store name must be a string.',
            'name.max' => 'The store name must not exceed 255 characters.',
            'alias.string' => 'The store alias must be a string.',
            'alias.max' => 'The store alias must not exceed 255 characters.',
            'alias.unique' => 'This store alias is already in use.',
            'email.email' => 'The email must be a valid email address.',
            'email.max' => 'The email must not exceed 255 characters.',
            'ussd_mobile_number.phone' => 'The USSD mobile number must be a valid international phone number (e.g., +26772000001).',
            'contact_mobile_number.phone' => 'The contact mobile number must be a valid international phone number (e.g., +26772000001).',
            'whatsapp_mobile_number.phone' => 'The WhatsApp mobile number must be a valid international phone number (e.g., +26772000001).',
            'description.max' => 'The description must not exceed 120 characters.',
            'reward_percentage_rate.numeric' => 'The reward percentage rate must be a number.',
            'reward_percentage_rate.min' => 'The reward percentage rate must be at least 0.',
            'reward_percentage_rate.max' => 'The reward percentage rate must not exceed 100.',
            'social_media_links.array' => 'The social links must be an array.',
            'social_media_links.*.active.boolean' => 'The social media link must be a boolean.',
            'social_media_links.*.name.required_with' => 'The social media link name is required.',
            'social_media_links.*.url.required_with' => 'The social media link URL is required.',
            'social_media_links.*.url.url' => 'The social media link URL must be a valid URL.',
            'country.size' => 'The country code must be 2 characters (e.g., BW).',
            'currency.size' => 'The currency code must be 3 characters (e.g., USD).',
            'language.size' => 'The language code must be 2 characters (e.g., en).',
            'distance_unit.enum' => 'The distance unit must be one of: ' . Arr::join(DistanceUnit::values(), ', ', ' or '),
            'weight_unit.enum' => 'The weight unit must be one of: ' . Arr::join(WeightUnit::values(), ', ', ' or '),
            'tax_method.enum' => 'The tax method must be one of: ' . Arr::join(TaxMethod::values(), ', ', ' or '),
            'tax_percentage_rate.numeric' => 'The tax percentage rate must be a number.',
            'tax_percentage_rate.min' => 'The tax percentage rate must be at least 0.',
            'tax_percentage_rate.max' => 'The tax percentage rate must not exceed 100.',
            'tax_id.max' => 'The tax ID must not exceed 50 characters.',
            'offline_message.max' => 'The offline message must not exceed 120 characters.',
            'sms_sender_name.max' => 'The SMS sender name must not exceed 11 characters.',
            'customer_section_heading.max' => 'The customer section heading must not exceed 25 characters.',
            'items_section_heading.max' => 'The items section heading must not exceed 25 characters.',
            'delivery_methods_section_heading.max' => 'The delivery methods section heading must not exceed 25 characters.',
            'delivery_schedule_title.max' => 'The delivery schedule title must not exceed 25 characters.',
            'delivery_address_title.max' => 'The delivery address title must not exceed 25 characters.',
            'tip_section_heading.max' => 'The tip section heading must not exceed 25 characters.',
            'promotions_section_heading.max' => 'The promotions section heading must not exceed 25 characters.',
            'cost_breakdown_section_heading.max' => 'The cost breakdown section heading must not exceed 25 characters.',
            'opening_hours.array' => 'The opening hours must be an array.',
            'opening_hours.*.day.required_with' => 'The day is required for each opening hour entry.',
            'opening_hours.*.day.in' => 'The day must be one of: ' . Arr::join(DaysOfTheWeek::values(), ', ', ' or '),
            'opening_hours.*.open.required_with' => 'The opening time is required for each opening hour entry.',
            'opening_hours.*.open.regex' => 'The opening time must be in HH:MM format (e.g., 09:00).',
            'opening_hours.*.close.required_with' => 'The closing time is required for each opening hour entry.',
            'opening_hours.*.close.regex' => 'The closing time must be in HH:MM format (e.g., 17:00).',
            'tips.array' => 'The tips must be an array.',
            'tips.*.numeric' => 'Each tip value must be a number.',
            'tips.*.min' => 'Each tip value must be at least 0.',
            'checkout_fees.array' => 'The checkout fees must be an array.',
            'checkout_fees.*.name.required_with' => 'The fee name is required for each checkout fee entry.',
            'checkout_fees.*.flat_rate.required_with' => 'The flat rate is required for each checkout fee entry.',
            'checkout_fees.*.flat_rate.numeric' => 'The flat rate must be a number.',
            'checkout_fees.*.flat_rate.min' => 'The flat rate must be at least 0.',
            'checkout_fees.*.percentage_rate.required_with' => 'The percentage rate is required for each checkout fee entry.',
            'checkout_fees.*.percentage_rate.numeric' => 'The percentage rate must be a number.',
            'checkout_fees.*.percentage_rate.min' => 'The percentage rate must be at least 0.',
            'checkout_fees.*.percentage_rate.max' => 'The percentage rate must not exceed 100.',
            'order_number_padding.integer' => 'The order number padding must be a integer.',
            'order_number_padding.min' => 'The order number padding must be at least 0.',
            'order_number_padding.max' => 'The order number padding must be at most 5.',
            'order_number_prefix.string' => 'The order number prefix must be a string.',
            'order_number_prefix.max' => 'The order number prefix must not exceed 255 characters.',
            'order_number_suffix.string' => 'The order number suffix must be a string.',
            'order_number_suffix.max' => 'The order number suffix must not exceed 255 characters.',
            'order_number_counter.integer' => 'The order number counter must be a integer.',
            'order_number_counter.min' => 'The order number counter must be at least 0.',
        ];
    }
}
