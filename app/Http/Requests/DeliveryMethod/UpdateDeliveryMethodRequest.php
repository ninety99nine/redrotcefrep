<?php

namespace App\Http\Requests\DeliveryMethod;

use Illuminate\Support\Arr;
use App\Enums\DaysOfTheWeek;
use App\Enums\DeliveryTimeUnit;
use Illuminate\Validation\Rule;
use App\Enums\DeliveryMethodFeeType;
use App\Enums\AutoGenerateTimeSlotsUnit;
use App\Enums\DeliveryMethodScheduleType;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliveryMethodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('deliveryMethod'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $deliveryMethod = $this->route('deliveryMethod');

        return [
            'active' => ['nullable', 'boolean'],
            'name' => [
                'sometimes', 'string', 'max:40',
                Rule::unique('delivery_methods')
                    ->ignore($deliveryMethod->id)
                    ->where(function ($query) use ($deliveryMethod) {
                        return $query->where('store_id', $this->input('store_id', $deliveryMethod->store_id));
                    }),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'currency' => ['nullable', 'string', 'size:3'],
            'qualify_on_minimum_grand_total' => ['nullable', 'boolean'],
            'minimum_grand_total' => ['nullable', 'numeric', 'min:0'],
            'offer_free_delivery_on_minimum_grand_total' => ['nullable', 'boolean'],
            'free_delivery_minimum_grand_total' => ['nullable', 'numeric', 'min:0'],
            'ask_for_an_address' => ['nullable', 'boolean'],
            'pin_location_on_map' => ['nullable', 'boolean'],
            'show_distance_on_invoice' => ['nullable', 'boolean'],
            'charge_fee' => ['nullable', 'boolean'],
            'fee_type' => ['nullable', Rule::enum(DeliveryMethodFeeType::class)],
            'percentage_fee_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'flat_fee_rate' => ['nullable', 'numeric', 'min:0'],
            'weight_categories' => ['nullable', 'array'],
            'distance_zones' => ['nullable', 'array'],
            'postal_code_zones' => ['nullable', 'array'],
            'fallback_fee_type' => ['nullable', Rule::enum(DeliveryMethodFeeType::class)],
            'fallback_percentage_fee_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fallback_flat_fee_rate' => ['nullable', 'numeric', 'min:0'],
            'set_schedule' => ['nullable', 'boolean'],
            'schedule_type' => ['nullable', Rule::enum(DeliveryMethodScheduleType::class)],
            'operational_hours.*.hours' => ['required', 'array', 'size:1'],
            'operational_hours.*.hours.*' => ['required', 'array', 'size:2'],
            'operational_hours.*.hours.*.0' => ['required', 'string', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'operational_hours.*.hours.*.1' => ['required', 'string', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'operational_hours.*.available' => ['required', 'boolean'],
            'auto_generate_time_slots' => ['nullable', 'boolean'],
            'time_slot_interval_value' => ['nullable', 'integer', 'min:1', 'max:255'],
            'time_slot_interval_unit' => ['nullable', Rule::enum(AutoGenerateTimeSlotsUnit::class)],
            'same_day_delivery' => ['nullable', 'boolean'],
            'require_minimum_notice_for_orders' => ['nullable', 'boolean'],
            'earliest_delivery_time_value' => ['nullable', 'integer', 'min:1', 'max:255'],
            'earliest_delivery_time_unit' => ['nullable', Rule::enum(DeliveryTimeUnit::class)],
            'restrict_maximum_notice_for_orders' => ['nullable', 'boolean'],
            'latest_delivery_time_value' => ['nullable', 'integer', 'min:1', 'max:255'],
            'set_daily_order_limit' => ['nullable', 'boolean'],
            'daily_order_limit' => ['nullable', 'integer', 'min:0', 'max:16777215'],
            'capture_additional_fields' => ['nullable', 'boolean'],
            'additional_fields' => ['nullable', 'array'],
            'position' => ['nullable', 'integer', 'min:0', 'max:255'],
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
            'name.string' => 'The delivery method name must be a string.',
            'name.max' => 'The delivery method name must not exceed 40 characters.',
            'description.max' => 'The description must not exceed 500 characters.',
            'currency.size' => 'The currency code must be 3 characters (e.g., USD).',
            'minimum_grand_total.numeric' => 'The minimum grand total must be a number.',
            'minimum_grand_total.min' => 'The minimum grand total must be at least 0.',
            'free_delivery_minimum_grand_total.numeric' => 'The free delivery minimum grand total must be a number.',
            'free_delivery_minimum_grand_total.min' => 'The free delivery minimum grand total must be at least 0.',
            'percentage_fee_rate.numeric' => 'The percentage fee rate must be a number.',
            'percentage_fee_rate.min' => 'The percentage fee rate must be at least 0.',
            'percentage_fee_rate.max' => 'The percentage fee rate must not exceed 99.99.',
            'flat_fee_rate.numeric' => 'The flat fee rate must be a number.',
            'flat_fee_rate.min' => 'The flat fee rate must be at least 0.',
            'fallback_percentage_fee_rate.numeric' => 'The fallback percentage fee rate must be a number.',
            'fallback_percentage_fee_rate.min' => 'The fallback percentage fee rate must be at least 0.',
            'fallback_percentage_fee_rate.max' => 'The fallback percentage fee rate must not exceed 99.99.',
            'fallback_flat_fee_rate.numeric' => 'The fallback flat fee rate must be a number.',
            'fallback_flat_fee_rate.min' => 'The fallback flat fee rate must be at least 0.',
            'operational_hours.required' => 'The operational hours are required.',
            'operational_hours.array' => 'The operational hours must be an array.',
            'operational_hours.size' => 'The operational hours must contain exactly 7 entries, one for each day of the week.',
            'operational_hours.*.hours.required' => 'The hours for each day are required.',
            'operational_hours.*.hours.array' => 'The hours for each day must be an array.',
            'operational_hours.*.hours.size' => 'Each day must have exactly one time slot.',
            'operational_hours.*.hours.*.required' => 'Each time slot must include an opening and closing time.',
            'operational_hours.*.hours.*.array' => 'Each time slot must be an array with two elements.',
            'operational_hours.*.hours.*.size' => 'Each time slot must contain exactly two times (open and close).',
            'operational_hours.*.hours.*.0.required' => 'The opening time for each day is required.',
            'operational_hours.*.hours.*.0.regex' => 'The opening time must be in HH:MM format (e.g., 08:00).',
            'operational_hours.*.hours.*.1.required' => 'The closing time for each day is required.',
            'operational_hours.*.hours.*.1.regex' => 'The closing time must be in HH:MM format (e.g., 16:00).',
            'operational_hours.*.available.required' => 'The availability for each day is required.',
            'operational_hours.*.available.boolean' => 'The availability for each day must be a boolean.',
            'time_slot_interval_value.min' => 'The time slot interval value must be at least 1.',
            'time_slot_interval_value.max' => 'The time slot interval value must not exceed 255.',
            'earliest_delivery_time_value.integer' => 'The earliest delivery time value must be an integer.',
            'earliest_delivery_time_value.min' => 'The earliest delivery time value must be at least 1.',
            'earliest_delivery_time_value.max' => 'The earliest delivery time value must not exceed 255.',
            'latest_delivery_time_value.integer' => 'The latest delivery time value must be an integer.',
            'latest_delivery_time_value.min' => 'The latest delivery time value must be at least 1.',
            'latest_delivery_time_value.max' => 'The latest delivery time value must not exceed 255.',
            'daily_order_limit.integer' => 'The daily order limit must be an integer.',
            'daily_order_limit.min' => 'The daily order limit must be at least 0.',
            'daily_order_limit.max' => 'The daily order limit must not exceed 16777215.',
            'weight_categories.array' => 'The weight categories must be an array.',
            'distance_zones.array' => 'The distance zones must be an array.',
            'postal_code_zones.array' => 'The postal code zones must be an array.',
            'additional_fields.array' => 'The additional fields must be an array.',
            'position.integer' => 'The position must be an integer.',
            'position.min' => 'The position must be at least 0.',
            'position.max' => 'The position must not exceed 255.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'fee_type.enum' => 'The fee type must be one of: ' . Arr::join(DeliveryMethodFeeType::values(), ', ', ' or '),
            'fallback_fee_type.enum' => 'The fallback fee type must be one of: ' . Arr::join(DeliveryMethodFeeType::values(), ', ', ' or '),
            'schedule_type.enum' => 'The schedule type must be one of: ' . Arr::join(DeliveryMethodScheduleType::values(), ', ', ' or '),
            'time_slot_interval_unit.enum' => 'The time slot interval unit must be one of: ' . Arr::join(AutoGenerateTimeSlotsUnit::values(), ', ', ' or '),
            'earliest_delivery_time_unit.enum' => 'The earliest delivery time unit must be one of: ' . Arr::join(DeliveryTimeUnit::values(), ', ', ' or '),
        ];
    }
}
