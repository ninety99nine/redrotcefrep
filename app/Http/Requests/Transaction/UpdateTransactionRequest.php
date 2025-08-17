<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use App\Enums\TransactionPaymentStatus;
use App\Enums\TransactionVerificationType;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('transaction'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'description' => ['sometimes', 'string', 'max:255'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'percentage' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'metadata' => ['sometimes', 'array'],
            'payment_method_id' => ['sometimes', 'uuid'],
            'created_using_auto_billing' => ['sometimes', 'boolean'],
            'customer_id' => ['sometimes', 'uuid'],
            'store_id' => ['sometimes', 'uuid'],
            'ai_assistant_id' => ['sometimes', 'uuid'],
            'payment_status' => ['sometimes', Rule::enum(TransactionPaymentStatus::class)],
            'failure_type' => ['required_if:payment_status,' . TransactionPaymentStatus::FAILED_PAYMENT->value, 'nullable', 'string', 'max:255'],
            'failure_reason' => ['required_if:payment_status,' . TransactionPaymentStatus::FAILED_PAYMENT->value, 'nullable', 'string', 'max:255'],
            'verification_type' => ['sometimes', Rule::enum(TransactionVerificationType::class)],
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
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description must not exceed 255 characters.',
            'currency.size' => 'The currency code must be 3 characters (e.g., USD).',
            'amount.numeric' => 'The amount must be a number.',
            'amount.min' => 'The amount must be at least 0.',
            'percentage.integer' => 'The percentage must be an integer.',
            'percentage.min' => 'The percentage must be at least 0.',
            'percentage.max' => 'The percentage must not exceed 100.',
            'metadata.array' => 'The metadata must be an array.',
            'payment_method_id.uuid' => 'The payment method ID must be a valid UUID.',
            'created_using_auto_billing.boolean' => 'The created using auto billing field must be a boolean.',
            'customer_id.uuid' => 'The customer ID must be a valid UUID.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'ai_assistant_id.uuid' => 'The AI assistant ID must be a valid UUID.',
            'payment_status.enum' => 'The payment status must be one of: ' . Arr::join(TransactionPaymentStatus::values(), ', ', ' or '),
            'failure_type.required_if' => 'The failure type is required when payment status is failed payment.',
            'failure_type.string' => 'The failure type must be a string.',
            'failure_type.max' => 'The failure type must not exceed 255 characters.',
            'failure_reason.required_if' => 'The failure reason is required when payment status is failed payment.',
            'failure_reason.string' => 'The failure reason must be a string.',
            'failure_reason.max' => 'The failure reason must not exceed 255 characters.',
            'verification_type.enum' => 'The verification type must be one of: ' . Arr::join(TransactionVerificationType::values(), ', ', ' or '),
        ];
    }
}
