<?php

namespace App\Http\Requests\Store;

use App\Models\Store;
use App\Enums\RateType;
use App\Enums\TaxMethod;
use App\Enums\WeightUnit;
use App\Enums\DistanceUnit;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CreateStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Store::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'alias' => ['sometimes', 'string', 'max:255', Rule::unique('stores', 'alias')],
            'email' => ['nullable', 'email', 'max:255'],
            'ussd_mobile_number' => ['nullable', 'phone:INTERNATIONAL', 'max:20'],
            'whatsapp_mobile_number' => ['nullable', 'phone:INTERNATIONAL', 'max:20'],
            'call_to_action' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'qr_code_file_path' => ['nullable', 'string', 'max:255'],
            'offer_rewards' => ['nullable', 'boolean'],
            'reward_percentage_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
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
            'opening_hours.*.available' => ['required_with:opening_hours', 'boolean'],
            'opening_hours.*.hours' => ['required_with:opening_hours', 'array'],
            'opening_hours.*.hours.*' => ['required_with:opening_hours', 'array'],
            'opening_hours.*.hours.*.*' => ['required_with:opening_hours', 'string', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'online' => ['nullable', 'boolean'],
            'offline_message' => ['nullable', 'string', 'max:120'],
            'sms_sender_name' => ['nullable', 'string', 'max:11'],
            'show_background' => ['nullable', 'boolean'],
            'bg_color' => ['nullable', 'string', 'max:7'],
            'order_number_padding' => ['nullable', 'integer', 'min:0', 'max:5'],
            'order_number_counter' => ['nullable', 'integer', 'min:0'],
            'order_number_prefix' => ['nullable', 'string', 'max:255'],
            'order_number_suffix' => ['nullable', 'string', 'max:255'],
            'message_footer' => ['nullable', 'string', 'max:255'],
            'show_sms_channel' => ['nullable', 'boolean'],
            'show_line_channel' => ['nullable', 'boolean'],
            'skip_payment_page' => ['nullable', 'boolean'],
            'show_whatsapp_channel' => ['nullable', 'boolean'],
            'show_telegram_channel' => ['nullable', 'boolean'],
            'show_messenger_channel' => ['nullable', 'boolean'],
            'line_channel_username' => ['nullable', 'string', 'max:255'],
            'telegram_channel_username' => ['nullable', 'string', 'max:255'],
            'messenger_channel_username' => ['nullable', 'string', 'max:255'],
            'invoice_show_logo' => ['nullable', 'boolean'],
            'invoice_show_qr_code' => ['nullable', 'boolean'],
            'invoice_header' => ['nullable', 'string', 'max:255'],
            'invoice_footer' => ['nullable', 'string', 'max:255'],
            'invoice_company_name' => ['nullable', 'string', 'max:255'],
            'invoice_company_email' => ['nullable', 'email', 'max:255'],
            'invoice_company_mobile_number' => ['nullable', 'phone:INTERNATIONAL', 'max:20'],
            'tips' => ['nullable', 'array'],
            'checkout_fees' => ['nullable', 'array'],
            'checkout_fees.*.name' => ['required', 'string', 'max:40'],
            'checkout_fees.*.rate_type' => ['required', Rule::enum(RateType::class)],
            'checkout_fees.*.flat_rate' => ['required_if:checkout_fees.*.rate_type,flat', 'numeric', 'min:0'],
            'checkout_fees.*.percentage_rate' => ['required_if:checkout_fees.*.rate_type,percentage', 'numeric', 'min:0', 'max:100'],
            'combine_fees' => ['nullable', 'boolean'],
            'combine_discounts' => ['nullable', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:60'],
            'seo_description' => ['nullable', 'string', 'max:160'],
            'seo_keywords' => ['nullable', 'array'],
            'seo_keywords.*' => ['string', 'max:50'],
            'google_analytics_id' => ['nullable', 'string', 'max:20', 'regex:/^G-[A-Z0-9]+$/'],
            'meta_pixel_id' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'tiktok_pixel_id' => ['nullable', 'string', 'max:20', 'regex:/^[A-Z0-9]+$/'],
            'logo' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:5120'],
            'background_photo' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:5120'],
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
            'name.required' => 'The store name is required.',
            'name.string' => 'The store name must be a string.',
            'name.max' => 'The store name must not exceed 255 characters.',
            'alias.required' => 'The store alias is required.',
            'alias.string' => 'The store alias must be a string.',
            'alias.max' => 'The store alias must not exceed 255 characters.',
            'alias.unique' => 'This store alias is already in use.',
            'email.email' => 'The email must be a valid email address.',
            'email.max' => 'The email must not exceed 255 characters.',
            'ussd_mobile_number.phone' => 'The USSD mobile number must be a valid international phone number (e.g., +26772000001).',
            'ussd_mobile_number.max' => 'The USSD mobile number must not exceed 20 characters.',
            'whatsapp_mobile_number.phone' => 'The WhatsApp mobile number must be a valid international phone number (e.g., +26772000001).',
            'whatsapp_mobile_number.max' => 'The WhatsApp mobile number must not exceed 20 characters.',
            'call_to_action.string' => 'The call to action must be a string.',
            'call_to_action.max' => 'The call to action must not exceed 255 characters.',
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description must not exceed 500 characters.',
            'qr_code_file_path.string' => 'The QR code file path must be a string.',
            'qr_code_file_path.max' => 'The QR code file path must not exceed 255 characters.',
            'offer_rewards.boolean' => 'The offer rewards field must be a boolean.',
            'reward_percentage_rate.numeric' => 'The reward percentage rate must be a number.',
            'reward_percentage_rate.min' => 'The reward percentage rate must be at least 0.',
            'reward_percentage_rate.max' => 'The reward percentage rate must not exceed 100.',
            'country.string' => 'The country code must be a string.',
            'country.size' => 'The country code must be 2 characters (e.g., BW).',
            'currency.string' => 'The currency code must be a string.',
            'currency.size' => 'The currency code must be 3 characters (e.g., USD).',
            'language.string' => 'The language code must be a string.',
            'language.size' => 'The language code must be 2 characters (e.g., en).',
            'distance_unit.enum' => 'The distance unit must be one of: ' . Arr::join(DistanceUnit::values(), ', ', ' or '),
            'weight_unit.enum' => 'The weight unit must be one of: ' . Arr::join(WeightUnit::values(), ', ', ' or '),
            'tax_method.enum' => 'The tax method must be one of: ' . Arr::join(TaxMethod::values(), ', ', ' or '),
            'tax_percentage_rate.numeric' => 'The tax percentage rate must be a number.',
            'tax_percentage_rate.min' => 'The tax percentage rate must be at least 0.',
            'tax_percentage_rate.max' => 'The tax percentage rate must not exceed 100.',
            'tax_id.string' => 'The tax ID must be a string.',
            'tax_id.max' => 'The tax ID must not exceed 50 characters.',
            'show_opening_hours.boolean' => 'The show opening hours field must be a boolean.',
            'allow_checkout_on_closed_hours.boolean' => 'The allow checkout on closed hours field must be a boolean.',
            'opening_hours.array' => 'The opening hours must be an array.',
            'opening_hours.*.available.boolean' => 'Each opening hours available field must be a boolean.',
            'opening_hours.*.hours.array' => 'Each opening hours hours field must be an array.',
            'opening_hours.*.hours.*.array' => 'Each opening hours time slot must be an array.',
            'opening_hours.*.hours.*.*.string' => 'Each opening hours time must be a string.',
            'opening_hours.*.hours.*.*.regex' => 'Each opening hours time must be in HH:MM format (e.g., 09:00).',
            'online.boolean' => 'The online field must be a boolean.',
            'offline_message.string' => 'The offline message must be a string.',
            'offline_message.max' => 'The offline message must not exceed 120 characters.',
            'show_background.boolean' => 'The show background field must be a boolean.',
            'bg_color.string' => 'The bg color must be a string.',
            'bg_color.max' => 'The bg color must not exceed 7 characters.',
            'sms_sender_name.string' => 'The SMS sender name must be a string.',
            'sms_sender_name.max' => 'The SMS sender name must not exceed 11 characters.',
            'order_number_padding.integer' => 'The order number padding must be an integer.',
            'order_number_padding.min' => 'The order number padding must be at least 0.',
            'order_number_padding.max' => 'The order number padding must be at most 5.',
            'order_number_counter.integer' => 'The order number counter must be an integer.',
            'order_number_counter.min' => 'The order number counter must be at least 0.',
            'order_number_prefix.string' => 'The order number prefix must be a string.',
            'order_number_prefix.max' => 'The order number prefix must not exceed 255 characters.',
            'order_number_suffix.string' => 'The order number suffix must be a string.',
            'order_number_suffix.max' => 'The order number suffix must not exceed 255 characters.',
            'message_footer.string' => 'The message footer must be a string.',
            'message_footer.max' => 'The message footer must not exceed 255 characters.',
            'show_sms_channel.boolean' => 'The show SMS channel field must be a boolean.',
            'show_line_channel.boolean' => 'The show Line channel field must be a boolean.',
            'skip_payment_page.boolean' => 'The bypass payment page field must be a boolean.',
            'show_whatsapp_channel.boolean' => 'The show WhatsApp channel field must be a boolean.',
            'show_telegram_channel.boolean' => 'The show Telegram channel field must be a boolean.',
            'show_messenger_channel.boolean' => 'The show Messenger channel field must be a boolean.',
            'line_channel_username.string' => 'The Line channel username must be a string.',
            'line_channel_username.max' => 'The Line channel username must not exceed 255 characters.',
            'telegram_channel_username.string' => 'The Telegram channel username must be a string.',
            'telegram_channel_username.max' => 'The Telegram channel username must not exceed 255 characters.',
            'messenger_channel_username.string' => 'The Messenger channel username must be a string.',
            'messenger_channel_username.max' => 'The Messenger channel username must not exceed 255 characters.',
            'invoice_show_logo.boolean' => 'The invoice show logo field must be a boolean.',
            'invoice_show_qr_code.boolean' => 'The invoice show QR code field must be a boolean.',
            'invoice_header.string' => 'The invoice header must be a string.',
            'invoice_header.max' => 'The invoice header must not exceed 255 characters.',
            'invoice_footer.string' => 'The invoice footer must be a string.',
            'invoice_footer.max' => 'The invoice footer must not exceed 255 characters.',
            'invoice_company_name.string' => 'The invoice company name must be a string.',
            'invoice_company_name.max' => 'The invoice company name must not exceed 255 characters.',
            'invoice_company_email.email' => 'The invoice company email must be a valid email address.',
            'invoice_company_email.max' => 'The invoice company email must not exceed 255 characters.',
            'invoice_company_mobile_number.phone' => 'The invoice company mobile number must be a valid international phone number (e.g., +26772000001).',
            'invoice_company_mobile_number.max' => 'The invoice company mobile number must not exceed 20 characters.',
            'tips.array' => 'The tips must be an array.',
            'checkout_fees.array' => 'The checkout fees must be an array.',
            'checkout_fees.*.name.required' => 'The fee name is required.',
            'checkout_fees.*.name.string' => 'The fee name must be a string.',
            'checkout_fees.*.name.max' => 'The fee name must not exceed 40 characters.',
            'checkout_fees.*.rate_type.required' => 'The fee type is required.',
            'checkout_fees.*.rate_type.enum' => 'The fee type must be either flat or percentage.',
            'checkout_fees.*.flat_rate.required_if' => 'The flat rate is required for a flat fee type.',
            'checkout_fees.*.flat_rate.numeric' => 'The flat rate must be a number.',
            'checkout_fees.*.flat_rate.min' => 'The flat rate must be at least 0.',
            'checkout_fees.*.percentage_rate.required_if' => 'The percentage rate is required for a percentage fee type.',
            'checkout_fees.*.percentage_rate.numeric' => 'The percentage rate must be a number.',
            'checkout_fees.*.percentage_rate.min' => 'The percentage rate must be at least 0.',
            'checkout_fees.*.percentage_rate.max' => 'The percentage rate must not exceed 100.',
            'seo_title.string' => 'The SEO title must be a string.',
            'seo_title.max' => 'The SEO title must not exceed 60 characters.',
            'seo_description.string' => 'The SEO description must be a string.',
            'seo_description.max' => 'The SEO description must not exceed 160 characters.',
            'seo_keywords.array' => 'The SEO keywords must be an array.',
            'seo_keywords.*.string' => 'Each SEO keyword must be a string.',
            'seo_keywords.*.max' => 'Each SEO keyword must not exceed 50 characters.',
            'google_analytics_id.string' => 'The Google Analytics ID must be a string.',
            'google_analytics_id.max' => 'The Google Analytics ID must not exceed 20 characters.',
            'google_analytics_id.regex' => 'The Google Analytics ID must be in the format G-XXXXXXXXXX.',
            'meta_pixel_id.string' => 'The Meta Pixel ID must be a string.',
            'meta_pixel_id.max' => 'The Meta Pixel ID must not exceed 20 characters.',
            'meta_pixel_id.regex' => 'The Meta Pixel ID must be a numeric string.',
            'tiktok_pixel_id.string' => 'The TikTok Pixel ID must be a string.',
            'tiktok_pixel_id.max' => 'The TikTok Pixel ID must not exceed 20 characters.',
            'tiktok_pixel_id.regex' => 'The TikTok Pixel ID must be an alphanumeric string.',
            'logo.file' => 'The logo must be a valid file.',
            'logo.mimes' => 'The logo must be a JPEG, PNG, JPG, GIF, or SVG.',
            'logo.max' => 'The logo size must not exceed 5MB.',
            'background_photo.file' => 'The logo must be a valid file.',
            'background_photo.mimes' => 'The logo must be a JPEG, PNG, JPG, GIF, or SVG.',
            'background_photo.max' => 'The logo size must not exceed 5MB.'
        ];
    }
}
