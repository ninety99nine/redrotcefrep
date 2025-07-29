<?php

namespace App\Http\Requests\Order;

use App\Models\Store;
use App\Enums\RateType;
use App\Enums\Association;
use App\Enums\OrderStatus;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use App\Enums\OrderPaymentStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder;
use App\Enums\DeliveryMethodScheduleType;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('order'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $store = $this->input('store_id') ? Store::find($this->input('store_id')) : null;
        $deliveryMethod = $store && $this->input('delivery_method_id') ? $store->deliveryMethods()->active()->find($this->input('delivery_method_id')) : null;

        $requiresDate = $deliveryMethod && $deliveryMethod->set_schedule;
        $requiresTimeslot = $requiresDate && $deliveryMethod->schedule_type === DeliveryMethodScheduleType::DATE_AND_TIME->value;

        return [
            'association' => ['sometimes', Rule::in([Association::SHOPPER->value, Association::TEAM_MEMBER->value])],
            'store_id' => ['required', 'uuid', Rule::exists('stores', 'id')],
            'guest_id' => [Rule::requiredIf(!Auth::user()), 'uuid'],
            'cart_products' => ['sometimes', 'array'],
            'cart_products.*' => ['array'],
            'cart_products.*.id' => ['nullable', 'uuid'],
            'cart_products.*.quantity' => ['required', 'numeric', 'min:1'],
            'cart_promotions' => ['sometimes', 'array'],
            'cart_promotions.*' => ['array'],
            'cart_promotions.*.id' => ['nullable', 'uuid'],
            'cart_fees' => ['sometimes', 'array'],
            'cart_fees.*' => ['array'],
            'cart_fees.*.name' => ['nullable', 'string', 'max:255'],
            'cart_fees.*.active' => ['required', 'boolean'],
            'cart_fees.*.rate_type' => ['required', Rule::in([RateType::FLAT->value, RateType::PERCENTAGE->value])],
            'cart_fees.*.flat_rate' => ['required_if:cart_fees.*.rate_type,' . RateType::FLAT->value, 'numeric', 'min:0'],
            'cart_fees.*.percentage_rate' => ['required_if:cart_fees.*.rate_type,' . RateType::PERCENTAGE->value, 'numeric', 'min:0', 'max:100'],
            'cart_promotion_code' => ['nullable', 'string', 'max:255'],
            'tip_flat_rate' => ['nullable', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'tip_percentage_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'adjustment' => ['nullable', 'numeric', 'regex:/^-?\d+(\.\d{1,2})?$/'],
            'delivery_address' => ['nullable', 'array'],
            'delivery_address.address_line' => ['required_with:delivery_address', 'string', 'max:255'],
            'delivery_address.address_line2' => ['nullable', 'string', 'max:255'],
            'delivery_address.city' => ['nullable', 'string', 'max:100'],
            'delivery_address.state' => ['nullable', 'string', 'max:100'],
            'delivery_address.postal_code' => ['nullable', 'string', 'max:20'],
            'delivery_address.country' => ['nullable', 'string', 'size:2'],
            'delivery_address.place_id' => ['nullable', 'string', 'max:255'],
            'delivery_address.latitude' => ['nullable', 'numeric', 'min:-90', 'max:90'],
            'delivery_address.longitude' => ['nullable', 'numeric', 'min:-180', 'max:180'],
            'delivery_address.description' => ['nullable', 'string'],
            'delivery_date' => $requiresDate ? ['nullable', 'date'] : ['exclude'],
            'delivery_timeslot' => $requiresTimeslot ? ['nullable', 'string', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d - (?:[01]\d|2[0-3]):[0-5]\d$/'] : ['exclude'],
            'delivery_method_id' => ['nullable', 'uuid', Rule::exists('delivery_methods', 'id')->where(function (Builder $query) {
                $query->where('store_id', $this->input('store_id'))->where('active', 1);
            })],
            'customer_first_name' => ['nullable', 'string', 'min:1', 'max:255'],
            'customer_last_name' => ['nullable', 'string', 'min:1', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_mobile_number' => ['nullable', 'string', 'max:20', 'phone:INTERNATIONAL'],
            'customer_birthday' => ['nullable', 'date', 'before:today'],
            'remark' => ['nullable', 'string'],
            'internal_note' => ['nullable', 'string'],
            'collection_note' => ['nullable', 'string'],
            'tracking_number' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(OrderStatus::class)],
            'courier_id' => ['nullable', 'uuid', 'exists:couriers,id'],
            'cancellation_reason' => ['nullable', 'string', 'max:255'],
            'assigned_to_user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'payment_status' => ['nullable', Rule::enum(OrderPaymentStatus::class)],
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
            'association.in' => 'The association must be one of: ' . Arr::join([Association::SHOPPER->value, Association::TEAM_MEMBER->value], ', ', ' or '),
            'store_id.required' => 'The store ID is required.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'store_id.exists' => 'The specified store does not exist.',
            'guest_id.required' => 'The guest ID is required when not authenticated.',
            'guest_id.uuid' => 'The guest ID must be a valid UUID.',
            'cart_products.array' => 'The cart products must be an array.',
            'cart_products.*.required' => 'Each cart product must be provided.',
            'cart_products.*.array' => 'Each cart product must be an array.',
            'cart_products.*.id.uuid' => 'Each cart product ID must be a valid UUID.',
            'cart_products.*.quantity.required' => 'The quantity for each cart product is required.',
            'cart_products.*.quantity.numeric' => 'The quantity for each cart product must be a number.',
            'cart_products.*.quantity.min' => 'The quantity for each cart product must be at least 1.',
            'cart_promotions.array' => 'The cart promotions must be an array.',
            'cart_promotions.*.required' => 'Each cart promotion must be provided.',
            'cart_promotions.*.array' => 'Each cart promotion must be an array.',
            'cart_promotions.*.id.uuid' => 'Each cart promotion ID must be a valid UUID.',
            'cart_fees.array' => 'The cart fees must be an array.',
            'cart_fees.*.required' => 'Each cart fee must be provided.',
            'cart_fees.*.array' => 'Each cart fee must be an array.',
            'cart_fees.*.name.required' => 'The name for each cart fee is required.',
            'cart_fees.*.name.string' => 'The name for each cart fee must be a string.',
            'cart_fees.*.name.max' => 'The name for each cart fee must not exceed 255 characters.',
            'cart_fees.*.active.required' => 'The active status for each cart fee is required.',
            'cart_fees.*.active.boolean' => 'The active status for each cart fee must be a boolean.',
            'cart_fees.*.rate_type.required' => 'The rate type for each cart fee is required.',
            'cart_fees.*.rate_type.in' => 'The rate type for each cart fee must be one of: ' . Arr::join(RateType::values(), ', ', ' or '),
            'cart_fees.*.flat_rate.required_if' => 'The flat rate for each cart fee is required when the rate type is flat.',
            'cart_fees.*.flat_rate.numeric' => 'The flat rate for each cart fee must be a number.',
            'cart_fees.*.flat_rate.min' => 'The flat rate for each cart fee must be at least 0.',
            'cart_fees.*.percentage_rate.required_if' => 'The percentage rate for each cart fee is required when the rate type is percentage.',
            'cart_fees.*.percentage_rate.numeric' => 'The percentage rate for each cart fee must be a number.',
            'cart_fees.*.percentage_rate.min' => 'The percentage rate for each cart fee must be at least 0.',
            'cart_fees.*.percentage_rate.max' => 'The percentage rate for each cart fee must not exceed 100.',
            'cart_promotion_code.string' => 'The cart promotion code must be a string.',
            'cart_promotion_code.max' => 'The cart promotion code must not exceed 255 characters.',
            'tip_flat_rate.numeric' => 'The tip flat rate must be a number.',
            'tip_flat_rate.min' => 'The tip flat rate must be at least 0.',
            'tip_flat_rate.regex' => 'The tip flat rate must have up to 2 decimal places.',
            'tip_percentage_rate.numeric' => 'The tip percentage rate must be a number.',
            'tip_percentage_rate.min' => 'The tip percentage rate must be at least 0.',
            'tip_percentage_rate.max' => 'The tip percentage rate must not exceed 100.',
            'adjustment.numeric' => 'The adjustment must be a number.',
            'adjustment.regex' => 'The adjustment must have up to 2 decimal places.',
            'delivery_address.array' => 'The delivery address must be an array.',
            'delivery_address.address_line.required_with' => 'The address line is required when a delivery address is provided.',
            'delivery_address.address_line.string' => 'The address line must be a string.',
            'delivery_address.address_line.max' => 'The address line must not exceed 255 characters.',
            'delivery_address.address_line2.string' => 'The second address line must be a string.',
            'delivery_address.address_line2.max' => 'The second address line must not exceed 255 characters.',
            'delivery_address.city.string' => 'The city must be a string.',
            'delivery_address.city.max' => 'The city must not exceed 100 characters.',
            'delivery_address.state.string' => 'The state must be a string.',
            'delivery_address.state.max' => 'The state must not exceed 100 characters.',
            'delivery_address.postal_code.string' => 'The postal code must be a string.',
            'delivery_address.postal_code.max' => 'The postal code must not exceed 20 characters.',
            'delivery_address.country.string' => 'The country must be a string.',
            'delivery_address.country.size' => 'The country code must be exactly 2 characters.',
            'delivery_address.place_id.string' => 'The place ID must be a string.',
            'delivery_address.place_id.max' => 'The place ID must not exceed 255 characters.',
            'delivery_address.latitude.numeric' => 'The latitude must be a number.',
            'delivery_address.latitude.min' => 'The latitude must be at least -90.',
            'delivery_address.latitude.max' => 'The latitude must not exceed 90.',
            'delivery_address.longitude.numeric' => 'The longitude must be a number.',
            'delivery_address.longitude.min' => 'The longitude must be at least -180.',
            'delivery_address.longitude.max' => 'The longitude must not exceed 180.',
            'delivery_address.description.string' => 'The description must be a string.',
            'delivery_date.required' => 'The delivery date is required when a delivery method with scheduling is selected.',
            'delivery_date.date' => 'The delivery date must be a valid date.',
            'delivery_timeslot.required' => 'The delivery timeslot is required when a delivery method with date and time scheduling is selected.',
            'delivery_timeslot.string' => 'The delivery timeslot must be a string.',
            'delivery_timeslot.regex' => 'The delivery timeslot must be in the format HH:MM - HH:MM (e.g., 09:00 - 10:00).',
            'delivery_method_id.uuid' => 'The delivery method ID must be a valid UUID.',
            'delivery_method_id.exists' => 'The specified delivery method does not exist or is not active for the store.',
            'customer_email.email' => 'The customer email must be a valid email address.',
            'customer_email.max' => 'The customer email must not exceed 255 characters.',
            'customer_email.required' => 'The customer email is required when configured by the store.',
            'customer_mobile_number.string' => 'The customer mobile number must be a string.',
            'customer_mobile_number.max' => 'The customer mobile number must not exceed 20 characters.',
            'customer_mobile_number.phone' => 'The customer mobile number must be a valid international phone number.',
            'customer_mobile_number.required' => 'The customer mobile number is required when configured by the store.',
            'customer_first_name.required' => 'The customer first name is required when configured by the store.',
            'customer_first_name.string' => 'The customer first name must be a string.',
            'customer_first_name.min' => 'The customer first name must be at least 1 character.',
            'customer_first_name.max' => 'The customer first name must not exceed 255 characters.',
            'customer_last_name.required' => 'The customer last name is required when configured by the store.',
            'customer_last_name.string' => 'The customer last name must be a string.',
            'customer_last_name.min' => 'The customer last name must be at least 1 character.',
            'customer_last_name.max' => 'The customer last name must not exceed 255 characters.',
            'customer_birthday.required' => 'The customer birthday is required when configured by the store.',
            'customer_birthday.date' => 'The customer birthday must be a valid date.',
            'customer_birthday.before' => 'The customer birthday must be a date before today.',
            'remark.string' => 'The remark must be a string.',
            'internal_note.string' => 'The internal note must be a string.',
            'collection_note.string' => 'The collection note must be a string.',
            'tracking_number.string' => 'The tracking number must be a string.',
            'tracking_number.max' => 'The tracking number must not exceed 255 characters.',
            'status.enum' => 'The status must be a valid order status.',
            'courier_id.uuid' => 'The courier ID must be a valid UUID.',
            'courier_id.exists' => 'The specified courier does not exist.',
            'cancellation_reason.string' => 'The cancellation reason must be a string.',
            'cancellation_reason.max' => 'The cancellation reason must not exceed 255 characters.',
            'assigned_to_user_id.uuid' => 'The assigned user ID must be a valid UUID.',
            'assigned_to_user_id.exists' => 'The specified assigned user does not exist.',
            'payment_status.enum' => 'The payment status must be a valid order payment status.',
        ];
    }
}

