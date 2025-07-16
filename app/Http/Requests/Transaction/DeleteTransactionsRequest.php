<?php

namespace App\Http\Requests\Transaction;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;

class DeleteTransactionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', Transaction::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'transaction_ids' => ['required', 'array', 'min:1'],
            'transaction_ids.*' => ['uuid', 'exists:transactions,id'],
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
            'transaction_ids.required' => 'The transaction IDs are required.',
            'transaction_ids.array' => 'The transaction IDs must be an array.',
            'transaction_ids.min' => 'At least one transaction ID is required.',
            'transaction_ids.*.uuid' => 'Each transaction ID must be a valid UUID.',
            'transaction_ids.*.exists' => 'One or more transaction IDs do not exist.',
        ];
    }
}
