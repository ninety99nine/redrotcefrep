<?php

namespace App\Http\Requests\AutoBillingSchedule;

use App\Enums\Association;
use App\Models\AutoBillingSchedule;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowAutoBillingSchedulesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', AutoBillingSchedule::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'uuid'],
            'active' => ['nullable', 'boolean'],
            'association' => ['sometimes', Rule::enum(Association::class)->only([Association::SUPER_ADMIN])],
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
            'user_id.uuid' => 'The user ID must be a valid UUID.',
            'association.enum' => 'The association must be one of: ' . Arr::join([Association::SUPER_ADMIN->value], ', ', ' or '),
        ];
    }
}
