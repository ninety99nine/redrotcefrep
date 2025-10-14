<?php

namespace App\Http\Requests\Promotion;

use App\Enums\RateType;
use App\Models\Promotion;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePromotionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('updateAny', Promotion::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'uuid'],
            'promotions' => ['required', 'array', 'min:1'],
            'promotions.*.id' => ['required', 'uuid'],
            'promotions.*.name' => ['sometimes', 'string', 'max:60'],
            'promotions.*.description' => ['nullable', 'string', 'max:500'],
            'promotions.*.active' => ['nullable', 'boolean'],
            'promotions.*.offer_discount' => ['nullable', 'boolean'],
            'promotions.*.discount_rate_type' => ['nullable', Rule::enum(RateType::class)],
            'promotions.*.discount_percentage_rate' => ['nullable', 'numeric', 'min:0', 'max:99.99'],
            'promotions.*.discount_flat_rate' => ['nullable', 'numeric', 'min:0'],
            'promotions.*.offer_free_delivery' => ['nullable', 'boolean'],
            'promotions.*.activate_using_code' => ['nullable', 'boolean'],
            'promotions.*.code' => ['nullable', 'string', 'max:20', function ($attribute, $value, $fail) {
                $index = explode('.', $attribute)[1] ?? null;
                $promotionId = $this->input("promotions.$index.id");

                if ($value && Promotion::where('code', $value)
                    ->where('id', '!=', $promotionId)
                    ->exists()) {
                    $fail("The code {$value} is already in use.");
                }
            }],
            'promotions.*.activate_using_minimum_grand_total' => ['nullable', 'boolean'],
            'promotions.*.minimum_grand_total' => ['nullable', 'numeric', 'min:0'],
            'promotions.*.activate_using_minimum_total_products' => ['nullable', 'boolean'],
            'promotions.*.minimum_total_products' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'promotions.*.activate_using_minimum_total_product_quantities' => ['nullable', 'boolean'],
            'promotions.*.minimum_total_product_quantities' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'promotions.*.activate_using_start_datetime' => ['nullable', 'boolean'],
            'promotions.*.start_datetime' => ['nullable', 'date'],
            'promotions.*.activate_using_end_datetime' => ['nullable', 'boolean'],
            'promotions.*.end_datetime' => ['nullable', 'date'],
            'promotions.*.activate_using_hours_of_day' => ['nullable', 'boolean'],
            'promotions.*.hours_of_day' => ['nullable', 'array'],
            'promotions.*.hours_of_day.*' => ['string', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'promotions.*.activate_using_days_of_the_week' => ['nullable', 'boolean'],
            'promotions.*.days_of_the_week' => ['nullable', 'array'],
            'promotions.*.days_of_the_week.*' => ['string', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'],
            'promotions.*.activate_using_days_of_the_month' => ['nullable', 'boolean'],
            'promotions.*.days_of_the_month' => ['nullable', 'array'],
            'promotions.*.days_of_the_month.*' => ['string', 'regex:/^(0[1-9]|[12]\d|3[01])$/'],
            'promotions.*.activate_using_months_of_the_year' => ['nullable', 'boolean'],
            'promotions.*.months_of_the_year' => ['nullable', 'array'],
            'promotions.*.months_of_the_year.*' => ['string', 'in:January,February,March,April,May,June,July,August,September,October,November,December'],
            'promotions.*.activate_for_new_customer' => ['nullable', 'boolean'],
            'promotions.*.activate_for_existing_customer' => ['nullable', 'boolean'],
            'promotions.*.activate_using_usage_limit' => ['nullable', 'boolean'],
            'promotions.*.remaining_quantity' => ['nullable', 'integer', 'min:0', 'max:16777215'],
            'promotions.*.user_id' => ['nullable', 'uuid'],
            'active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($this->input('promotions', []) as $index => $promotion) {
                $discountPercentageRate = $promotion['discount_percentage_rate'] ?? null;
                $discountFlatRate = $promotion['discount_flat_rate'] ?? null;

                if (!is_null($discountPercentageRate) && !is_null($discountFlatRate)) {
                    $validator->errors()->add(
                        "promotions.$index.discount_percentage_rate",
                        'Only one discount type (percentage or flat amount) can be provided.'
                    );
                }
            }
        });
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
            'promotions.required' => 'At least one promotion is required.',
            'promotions.array' => 'The promotions must be an array.',
            'promotions.min' => 'At least one promotion must be provided.',
            'promotions.*.id.required' => 'Each promotion must have an ID.',
            'promotions.*.id.uuid' => 'Each promotion ID must be a valid UUID.',
            'promotions.*.name.string' => 'The promotion name must be a string.',
            'promotions.*.name.max' => 'The promotion name must not exceed 60 characters.',
            'promotions.*.description.string' => 'The description must be a string.',
            'promotions.*.description.max' => 'The description must not exceed 500 characters.',
            'promotions.*.code.string' => 'The promotion code must be a string.',
            'promotions.*.code.max' => 'The promotion code must not exceed 20 characters.',
            'promotions.*.discount_percentage_rate.numeric' => 'The discount percentage rate must be a number.',
            'promotions.*.discount_percentage_rate.min' => 'The discount percentage rate must be at least 0.',
            'promotions.*.discount_percentage_rate.max' => 'The discount percentage rate must not exceed 99.99.',
            'promotions.*.discount_flat_rate.numeric' => 'The discount flat rate must be a number.',
            'promotions.*.discount_flat_rate.min' => 'The discount flat rate must be at least 0.',
            'promotions.*.minimum_grand_total.numeric' => 'The minimum grand total must be a number.',
            'promotions.*.minimum_grand_total.min' => 'The minimum grand total must be at least 0.',
            'promotions.*.minimum_total_products.integer' => 'The minimum total products must be an integer.',
            'promotions.*.minimum_total_products.min' => 'The minimum total products must be at least 1.',
            'promotions.*.minimum_total_products.max' => 'The minimum total products must not exceed 65535.',
            'promotions.*.minimum_total_product_quantities.integer' => 'The minimum total product quantities must be an integer.',
            'promotions.*.minimum_total_product_quantities.min' => 'The minimum total product quantities must be at least 1.',
            'promotions.*.minimum_total_product_quantities.max' => 'The minimum total product quantities must not exceed 65535.',
            'promotions.*.remaining_quantity.integer' => 'The remaining quantity must be an integer.',
            'promotions.*.remaining_quantity.min' => 'The remaining quantity must be at least 0.',
            'promotions.*.remaining_quantity.max' => 'The remaining quantity must not exceed 16777215.',
            'promotions.*.hours_of_day.array' => 'The hours of day must be an array.',
            'promotions.*.hours_of_day.*.regex' => 'Each hour of day must be in HH:MM format (e.g., 09:00).',
            'promotions.*.days_of_the_week.array' => 'The days of the week must be an array.',
            'promotions.*.days_of_the_week.*.in' => 'Each day of the week must be one of: Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, or Sunday',
            'promotions.*.days_of_the_month.array' => 'The days of the month must be an array.',
            'promotions.*.days_of_the_month.*.regex' => 'Each day of the month must be a two-digit string (e.g., 01, 15).',
            'promotions.*.months_of_the_year.array' => 'The months of the year must be an array.',
            'promotions.*.months_of_the_year.*.in' => 'Each month of the year must be one of: January, February, March, April, May, June, July, August, September, October, November, or December',
            'promotions.*.user_id.uuid' => 'The user ID must be a valid UUID.',
            'promotions.*.discount_rate_type.enum' => 'The discount rate type must be one of: ' . Arr::join(RateType::values(), ', ', ' or '),
            'active.boolean' => 'The active field must be a boolean.',
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description must not exceed 500 characters.',
        ];
    }
}
