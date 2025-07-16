<?php

namespace App\Http\Requests\Promotion;

use App\Enums\RateType;
use Illuminate\Support\Arr;
use App\Enums\DaysOfTheWeek;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePromotionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('promotion'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $promotionId = $this->route('promotion')->id;

        return [
            'name' => ['sometimes', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:500'],
            'active' => ['nullable', 'boolean'],
            'offer_discount' => ['nullable', 'boolean'],
            'discount_rate_type' => ['nullable', Rule::enum(RateType::class)],
            'discount_percentage_rate' => ['nullable', 'numeric', 'min:0', 'max:99.99'],
            'discount_flat_rate' => ['nullable', 'numeric', 'min:0'],
            'offer_free_delivery' => ['nullable', 'boolean'],
            'activate_using_code' => ['nullable', 'boolean'],
            'code' => ['nullable', 'string', 'max:20', Rule::unique('promotions', 'code')->ignore($promotionId)],
            'activate_using_minimum_grand_total' => ['nullable', 'boolean'],
            'minimum_grand_total' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'activate_using_minimum_total_products' => ['nullable', 'boolean'],
            'minimum_total_products' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'activate_using_minimum_total_product_quantities' => ['nullable', 'boolean'],
            'minimum_total_product_quantities' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'activate_using_start_datetime' => ['nullable', 'boolean'],
            'start_datetime' => ['nullable', 'date'],
            'activate_using_end_datetime' => ['nullable', 'boolean'],
            'end_datetime' => ['nullable', 'date'],
            'activate_using_hours_of_day' => ['nullable', 'boolean'],
            'hours_of_day' => ['nullable', 'array'],
            'hours_of_day.*' => ['string', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'activate_using_days_of_the_week' => ['nullable', 'boolean'],
            'days_of_the_week' => ['nullable', 'array'],
            'days_of_the_week.*' => ['string', Rule::in(DaysOfTheWeek::values())],
            'activate_using_days_of_the_month' => ['nullable', 'boolean'],
            'days_of_the_month' => ['nullable', 'array'],
            'days_of_the_month.*' => ['integer', 'min:1', 'max:31'],
            'activate_using_months_of_the_year' => ['nullable', 'boolean'],
            'months_of_the_year' => ['nullable', 'array'],
            'months_of_the_year.*' => ['integer', 'min:1', 'max:12'],
            'activate_for_new_customer' => ['nullable', 'boolean'],
            'activate_for_existing_customer' => ['nullable', 'boolean'],
            'activate_using_usage_limit' => ['nullable', 'boolean'],
            'remaining_quantity' => ['nullable', 'integer', 'min:0', 'max:16777215'],
            'store_id' => ['sometimes', 'uuid', 'exists:stores,id'],
            'user_id' => ['nullable', 'uuid', 'exists:users,id'],
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
            'name.string' => 'The promotion name must be a string.',
            'name.max' => 'The promotion name must not exceed 60 characters.',
            'description.max' => 'The description must not exceed 500 characters.',
            'code.unique' => 'This promotion code is already in use.',
            'code.max' => 'The promotion code must not exceed 20 characters.',
            'discount_percentage_rate.numeric' => 'The discount percentage rate must be a number.',
            'discount_percentage_rate.min' => 'The discount percentage rate must be at least 0.',
            'discount_percentage_rate.max' => 'The discount percentage rate must not exceed 99.99.',
            'discount_flat_rate.numeric' => 'The discount flat rate must be a number.',
            'discount_flat_rate.min' => 'The discount flat rate must be at least 0.',
            'minimum_grand_total.numeric' => 'The minimum grand total must be a number.',
            'minimum_grand_total.min' => 'The minimum grand total must be at least 0.',
            'minimum_total_products.integer' => 'The minimum total products must be an integer.',
            'minimum_total_products.min' => 'The minimum total products must be at least 1.',
            'minimum_total_products.max' => 'The minimum total products must not exceed 65535.',
            'minimum_total_product_quantities.integer' => 'The minimum total product quantities must be an integer.',
            'minimum_total_product_quantities.min' => 'The minimum total product quantities must be at least 1.',
            'minimum_total_product_quantities.max' => 'The minimum total product quantities must not exceed 65535.',
            'remaining_quantity.integer' => 'The remaining quantity must be an integer.',
            'remaining_quantity.min' => 'The remaining quantity must be at least 0.',
            'remaining_quantity.max' => 'The remaining quantity must not exceed 16777215.',
            'hours_of_day.array' => 'The hours of day must be an array.',
            'hours_of_day.*.regex' => 'Each hour of day must be in HH:MM format (e.g., 09:00).',
            'days_of_the_week.array' => 'The days of the week must be an array.',
            'days_of_the_week.*.in' => 'Each day of the week must be one of: ' . Arr::join(DaysOfTheWeek::values(), ',', 'or'),
            'days_of_the_month.array' => 'The days of the month must be an array.',
            'days_of_the_month.*.integer' => 'Each day of the month must be an integer.',
            'days_of_the_month.*.min' => 'Each day of the month must be at least 1.',
            'days_of_the_month.*.max' => 'Each day of the month must not exceed 31.',
            'months_of_the_year.array' => 'The months of the year must be an array.',
            'months_of_the_year.*.integer' => 'Each month of the year must be an integer.',
            'months_of_the_year.*.min' => 'Each month of the year must be at least 1.',
            'months_of_the_year.*.max' => 'Each month of the year must not exceed 12.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'store_id.exists' => 'The specified store does not exist.',
            'user_id.uuid' => 'The user ID must be a valid UUID.',
            'user_id.exists' => 'The specified user does not exist.',
            'discount_rate_type.enum' => 'The discount rate type must be one of: ' . Arr::join(RateType::values(), ',', 'or'),
        ];
    }
}
