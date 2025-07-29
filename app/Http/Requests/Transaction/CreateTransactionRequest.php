<?php

namespace App\Http\Requests\Transaction;

use App\Models\Transaction;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use App\Enums\TransactionPaymentStatus;
use App\Enums\TransactionVerificationType;
use Illuminate\Foundation\Http\FormRequest;

class CreateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Transaction::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'description' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'size:3'],
            'amount' => ['required', 'numeric', 'min:0'],
            'percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'metadata' => ['nullable', 'array'],
            'payment_method_id' => ['nullable', 'uuid', 'exists:payment_methods,id'],
            'created_using_auto_billing' => ['nullable', 'boolean'],
            'customer_id' => ['nullable', 'uuid', 'exists:customers,id'],
            'store_id' => ['required', 'uuid', 'exists:stores,id'],
            'ai_assistant_id' => ['nullable', 'uuid', 'exists:ai_assistants,id'],
            'owner_id' => ['required', 'uuid'],
            'owner_type' => ['required', 'string', Rule::in(['pricing plan', 'store', 'order'])],
            'payment_status' => ['nullable', Rule::enum(TransactionPaymentStatus::class)],
            'failure_type' => ['required_if:payment_status,' . TransactionPaymentStatus::FAILED_PAYMENT->value, 'nullable', 'string', 'max:255'],
            'failure_reason' => ['required_if:payment_status,' . TransactionPaymentStatus::FAILED_PAYMENT->value, 'nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:5120'],
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
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a number.',
            'amount.min' => 'The amount must be at least 0.',
            'percentage.integer' => 'The percentage must be an integer.',
            'percentage.min' => 'The percentage must be at least 0.',
            'percentage.max' => 'The percentage must not exceed 100.',
            'metadata.array' => 'The metadata must be an array.',
            'payment_method_id.uuid' => 'The payment method ID must be a valid UUID.',
            'payment_method_id.exists' => 'The specified payment method does not exist.',
            'created_using_auto_billing.boolean' => 'The created using auto billing field must be a boolean.',
            'customer_id.uuid' => 'The customer ID must be a valid UUID.',
            'customer_id.exists' => 'The specified customer does not exist.',
            'store_id.required' => 'The store ID is required.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'store_id.exists' => 'The specified store does not exist.',
            'ai_assistant_id.uuid' => 'The AI assistant ID must be a valid UUID.',
            'ai_assistant_id.exists' => 'The specified AI assistant does not exist.',
            'owner_id.required' => 'The owner ID is required.',
            'owner_id.uuid' => 'The owner ID must be a valid UUID.',
            'owner_type.required' => 'The owner type is required.',
            'owner_type.in' => 'The owner type must be one of: ' . Arr::join(['pricing plan', 'store', 'order'], ', ', ' or '),
            'payment_status.enum' => 'The payment status must be one of: ' . Arr::join(TransactionPaymentStatus::values(), ', ', ' or '),
            'failure_type.required_if' => 'The failure type is required when payment status is failed payment.',
            'failure_type.string' => 'The failure type must be a string.',
            'failure_type.max' => 'The failure type must not exceed 255 characters.',
            'failure_reason.required_if' => 'The failure reason is required when payment status is failed payment.',
            'failure_reason.string' => 'The failure reason must be a string.',
            'failure_reason.max' => 'The failure reason must not exceed 255 characters.',
            'photo.file' => 'The photo must be a valid file.',
            'photo.mimes' => 'The photo must be a JPEG, PNG, JPG, GIF, or SVG.',
            'photo.max' => 'The photo size must not exceed 5MB.',
        ];
    }
}
