<?php

namespace App\Http\Requests\Store;

use App\Enums\Platform;
use Illuminate\Support\Arr;
use App\Enums\InsightPeriod;
use App\Enums\InsightCategory;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowStoreInsightsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('view', $this->route('store'));
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('categories') && is_string($this->input('categories'))) {
            $categories = array_filter(array_map('trim', explode(',', $this->input('categories'))));
            $this->merge([
                'categories' => $categories,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'categories' => ['nullable', 'array'],
            'categories.*' => [Rule::enum(InsightCategory::class)],
            'platform' => ['nullable', Rule::enum(Platform::class)],
            'period' => ['nullable', Rule::enum(InsightPeriod::class)]
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
            'categories.array' => 'The categories must be an array.',
            'categories.*.enum' => 'Each category must be one of: ' . Arr::join(InsightCategory::values(), ', ', ' or '),
            'platform.enum' => 'The platform must be one of: ' . Arr::join(Platform::values(), ', ', ' or '),
            'period.enum' => 'The period must be one of: ' . Arr::join(InsightPeriod::values(), ', ', ' or '),
        ];
    }
}
