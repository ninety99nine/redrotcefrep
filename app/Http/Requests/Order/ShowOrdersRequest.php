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
            'user_id' => ['sometimes', 'uuid', 'exists:users,id'],
            'order_id' => ['sometimes', 'uuid', 'exists:orders,id'],
            'store_id' => ['sometimes', 'uuid', 'exists:stores,id'],
            'customer_id' => ['sometimes', 'uuid', 'exists:customers,id'],
            'placed_by_user_id' => ['sometimes', 'uuid', 'exists:users,id'],
            'created_by_user_id' => ['sometimes', 'uuid', 'exists:users,id'],
            'assigned_to_user_id' => ['sometimes', 'uuid', 'exists:users,id'],
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
            'order_id.exists' => 'The specified order does not exist.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'store_id.exists' => 'The specified store does not exist.',
            'customer_id.uuid' => 'The customer ID must be a valid UUID.',
            'customer_id.exists' => 'The specified customer does not exist.',
            'association.enum' => 'The association must be one of: ' . Arr::join([Association::SUPER_ADMIN->value, Association::TEAM_MEMBER->value], ',', 'or'),
        ];
    }
}
