<?php

namespace App\Http\Requests\StoreQuota;

use App\Models\StoreQuota;
use App\Enums\Association;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowStoreQuotasRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', StoreQuota::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'store_quota_id' => ['sometimes', 'uuid'],
            'store_id' => ['sometimes', 'uuid'],
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
            'store_quota_id.uuid' => 'The store quota ID must be a valid UUID.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'association.enum' => 'The association must be one of: ' . Arr::join([Association::ASSOCIATED->value, Association::UNASSOCIATED->value], ', ', ' or '),
        ];
    }
}
