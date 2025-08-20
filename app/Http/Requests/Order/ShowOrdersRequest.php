<?php

namespace App\Http\Requests\Order;

use App\Models\Order;
use App\Enums\Association;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowOrdersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Order::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'order_id' => ['sometimes', 'uuid'],
            'store_id' => ['sometimes', 'uuid'],
            'customer_id' => ['sometimes', 'uuid'],
            'placed_by_user_id' => ['sometimes', 'uuid'],
            'created_by_user_id' => ['sometimes', 'uuid'],
            'assigned_to_user_id' => ['sometimes', 'uuid'],
            'association' => ['sometimes', Rule::enum(Association::class)->only([Association::SUPER_ADMIN, Association::TEAM_MEMBER])],
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
            'order_id.uuid' => 'The order ID must be a valid UUID.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'customer_id.uuid' => 'The customer ID must be a valid UUID.',
            'placed_by_user_id.uuid' => 'The placed by user ID must be a valid UUID.',
            'created_by_user_id.uuid' => 'The created by user ID must be a valid UUID.',
            'assigned_to_user_id.uuid' => 'The assigned to user ID must be a valid UUID.',
            'association.enum' => 'The association must be one of: ' . Arr::join([Association::SUPER_ADMIN->value, Association::TEAM_MEMBER->value], ', ', ' or '),
        ];
    }
}
