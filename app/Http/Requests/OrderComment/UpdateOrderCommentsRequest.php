<?php

namespace App\Http\Requests\OrderComment;

use App\Enums\OrderStatus;
use Illuminate\Support\Arr;
use App\Models\OrderComment;
use Illuminate\Validation\Rule;
use App\Enums\OrderPaymentStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderCommentsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('updateAny', OrderComment::class);
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
            'order_ids.*' => ['uuid'],
            'store_id' => ['sometimes', 'uuid'],
            'status' => ['sometimes', Rule::enum(OrderStatus::class)],
            'payment_status' => ['sometimes', Rule::enum(OrderPaymentStatus::class)],
            'assigned_to_user_id' => ['nullable', 'uuid'],
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
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'status.enum' => 'The status must be one of: ' . Arr::join(OrderStatus::values(), ', ', ' or '),
            'payment_status.enum' => 'The payment status must be one of: ' . Arr::join(OrderPaymentStatus::values(), ', ', ' or '),
            'assigned_to_user_id.uuid' => 'The assigned to user ID must be a valid UUID.',
        ];
    }
}
