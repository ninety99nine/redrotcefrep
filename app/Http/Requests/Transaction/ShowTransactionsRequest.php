<?php

namespace App\Http\Requests\Transaction;

use App\Enums\Association;
use App\Models\Transaction;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowTransactionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Transaction::class);
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
            'ai_assistant_id' => ['sometimes', 'uuid'],
            'payment_method_id' => ['sometimes', 'uuid'],
            'requested_by_user_id' => ['sometimes', 'uuid'],
            'manually_verified_by_user_id' => ['sometimes', 'uuid'],
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
            'order_id.uuid' => 'The order ID must be a valid UUID.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'customer_id.uuid' => 'The customer ID must be a valid UUID.',
            'ai_assistant_id.uuid' => 'The AI Assistant ID must be a valid UUID.',
            'payment_method_id.uuid' => 'The payment method ID must be a valid UUID.',
            'requested_by_user_id.uuid' => 'The requested by user ID must be a valid UUID.',
            'manually_verified_by_user_id.uuid' => 'The manually verified by user ID must be a valid UUID.',
            'association.enum' => 'The association must be one of: ' . Arr::join([Association::SUPER_ADMIN->value], ', ', ' or '),
        ];
    }
}
