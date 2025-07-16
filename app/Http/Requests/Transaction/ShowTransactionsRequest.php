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
            'transaction_id' => ['sometimes', 'uuid', 'exists:transactions,id'],
            'store_id' => ['sometimes', 'uuid', 'exists:stores,id'],
            'association' => ['sometimes', Rule::enum(Association::class)->only([Association::ASSOCIATED, Association::UNASSOCIATED])],
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
            'transaction_id.uuid' => 'The transaction ID must be a valid UUID.',
            'transaction_id.exists' => 'The specified transaction does not exist.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'store_id.exists' => 'The specified store does not exist.',
            'association.enum' => 'The association must be one of: ' . Arr::join([Association::ASSOCIATED->value, Association::UNASSOCIATED->value], ',', 'or'),
        ];
    }
}
