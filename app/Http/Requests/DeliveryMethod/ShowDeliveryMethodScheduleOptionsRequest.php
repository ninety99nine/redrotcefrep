<?php

namespace App\Http\Requests\DeliveryMethod;

use Illuminate\Support\Arr;
use App\Enums\DeliveryTimeUnit;
use Illuminate\Validation\Rule;
use App\Enums\AutoGenerateTimeSlotsUnit;
use App\Enums\DeliveryMethodScheduleType;
use Illuminate\Foundation\Http\FormRequest;

class ShowDeliveryMethodScheduleOptionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Public route
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'uuid', 'exists:stores,id'],
            'schedule_type' => ['required', Rule::enum(DeliveryMethodScheduleType::class)],
            'daily_order_limit' => ['required_if:set_daily_order_limit,true', 'integer', 'min:0', 'max:16777215'],
            'same_day_delivery' => ['required', 'boolean'],
            'operational_hours' => ['required', 'array', 'size:7'],
            'operational_hours.*.hours' => ['required', 'array', 'size:1'],
            'operational_hours.*.hours.*' => ['required', 'array', 'size:2'],
            'operational_hours.*.hours.*.0' => ['required', 'string', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'operational_hours.*.hours.*.1' => ['required', 'string', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'operational_hours.*.available' => ['required', 'boolean'],
            'set_daily_order_limit' => ['required', 'boolean'],
            'time_slot_interval_unit' => ['required', Rule::enum(AutoGenerateTimeSlotsUnit::class)],
            'auto_generate_time_slots' => ['required', 'boolean'],
            'time_slot_interval_value' => ['required_if:auto_generate_time_slots,true', 'integer', 'min:1', 'max:255'],
            'latest_delivery_time_value' => ['required_if:restrict_maximum_notice_for_orders,true', 'integer', 'min:1', 'max:255'],
            'earliest_delivery_time_unit' => ['required', Rule::enum(DeliveryTimeUnit::class)],
            'earliest_delivery_time_value' => ['required', 'integer', 'min:1', 'max:255'],
            'require_minimum_notice_for_orders' => ['required', 'boolean'],
            'restrict_maximum_notice_for_orders' => ['required', 'boolean'],
            'delivery_date' => ['nullable', 'date'],
            'show_all_dates' => ['sometimes', 'boolean'],
            'show_all_timeslots' => ['sometimes', 'boolean'],
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
            'store_id.required' => 'The store ID is required.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'store_id.exists' => 'The specified store does not exist.',
            'schedule_type.required' => 'The schedule type is required.',
            'schedule_type.enum' => 'The schedule type must be one of: ' . Arr::join(DeliveryMethodScheduleType::values(), ', ', ' or '),
            'daily_order_limit.required_if' => 'The daily order limit is required when set_daily_order_limit is true.',
            'daily_order_limit.integer' => 'The daily order limit must be an integer.',
            'daily_order_limit.min' => 'The daily order limit must be at least 0.',
            'daily_order_limit.max' => 'The daily order limit must not exceed 16777215.',
            'same_day_delivery.required' => 'The same day delivery option is required.',
            'same_day_delivery.boolean' => 'The same day delivery option must be a boolean.',
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
            'set_daily_order_limit.required' => 'The set daily order limit option is required.',
            'set_daily_order_limit.boolean' => 'The set daily order limit option must be a boolean.',
            'time_slot_interval_unit.required' => 'The time slot interval unit is required.',
            'time_slot_interval_unit.enum' => 'The time slot interval unit must be one of: ' . Arr::join(AutoGenerateTimeSlotsUnit::values(), ', ', ' or '),
            'auto_generate_time_slots.required' => 'The auto generate time slots option is required.',
            'auto_generate_time_slots.boolean' => 'The auto generate time slots option must be a boolean.',
            'time_slot_interval_value.required_if' => 'The time slot interval value is required when auto_generate_time_slots is true.',
            'time_slot_interval_value.integer' => 'The time slot interval value must be an integer.',
            'time_slot_interval_value.min' => 'The time slot interval value must be at least 1.',
            'time_slot_interval_value.max' => 'The time slot interval value must not exceed 255.',
            'latest_delivery_time_value.required_if' => 'The latest delivery time value is required when restrict_maximum_notice_for_orders is true.',
            'latest_delivery_time_value.integer' => 'The latest delivery time value must be an integer.',
            'latest_delivery_time_value.min' => 'The latest delivery time value must be at least 1.',
            'latest_delivery_time_value.max' => 'The latest delivery time value must not exceed 255.',
            'earliest_delivery_time_unit.required' => 'The earliest delivery time unit is required.',
            'earliest_delivery_time_unit.enum' => 'The earliest delivery time unit must be one of: ' . Arr::join(DeliveryTimeUnit::values(), ', ', ' or '),
            'earliest_delivery_time_value.required' => 'The earliest delivery time value is required.',
            'earliest_delivery_time_value.integer' => 'The earliest delivery time value must be an integer.',
            'earliest_delivery_time_value.min' => 'The earliest delivery time value must be at least 1.',
            'earliest_delivery_time_value.max' => 'The earliest delivery time value must not exceed 255.',
            'require_minimum_notice_for_orders.required' => 'The require minimum notice for orders option is required.',
            'require_minimum_notice_for_orders.boolean' => 'The require minimum notice for orders option must be a boolean.',
            'restrict_maximum_notice_for_orders.required' => 'The restrict maximum notice for orders option is required.',
            'restrict_maximum_notice_for_orders.boolean' => 'The restrict maximum notice for orders option must be a boolean.',
        ];
    }
}
