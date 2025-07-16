<?php

namespace App\Http\Requests\Order;

use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use App\Enums\OrderPaymentStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrdersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('updateAny', Order::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['uuid', 'exists:orders,id'],
            'store_id' => ['sometimes', 'uuid', 'exists:stores,id'],
            'status' => ['sometimes', Rule::enum(OrderStatus::class)],
            'payment_status' => ['sometimes', Rule::enum(OrderPaymentStatus::class)],
            'assigned_to_user_id' => ['nullable', 'uuid', 'exists:users,id'],
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
            'order_ids.required' => 'The order IDs are required.',
            'order_ids.array' => 'The order IDs must be an array.',
            'order_ids.min' => 'At least one order ID is required.',
            'order_ids.*.uuid' => 'Each order ID must be a valid UUID.',
            'order_ids.*.exists' => 'One or more order IDs do not exist.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'store_id.exists' => 'The specified store does not exist.',
            'status.enum' => 'The status must be one of: ' . Arr::join(OrderStatus::values(), ',', 'or'),
            'payment_status.enum' => 'The payment status must be one of: ' . Arr::join(OrderPaymentStatus::values(), ',', 'or'),
            'assigned_to_user_id.uuid' => 'The assigned to user ID must be a valid UUID.',
            'assigned_to_user_id.exists' => 'The specified assigned to user does not exist.',
        ];
    }
}
