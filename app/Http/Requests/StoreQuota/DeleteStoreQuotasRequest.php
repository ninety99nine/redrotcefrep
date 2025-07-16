<?php

namespace App\Http\Requests\StoreQuota;

use App\Models\StoreQuota;
use Illuminate\Foundation\Http\FormRequest;

class DeleteStoreQuotasRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', StoreQuota::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'store_quota_ids' => ['required', 'array', 'min:1'],
            'store_quota_ids.*' => ['uuid', 'exists:store_quotas,id'],
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
            'store_quota_ids.required' => 'The store quota IDs are required.',
            'store_quota_ids.array' => 'The store quota IDs must be an array.',
            'store_quota_ids.min' => 'At least one store quota ID is required.',
            'store_quota_ids.*.uuid' => 'Each store quota ID must be a valid UUID.',
            'store_quota_ids.*.exists' => 'One or more store quota IDs do not exist.',
        ];
    }
}
