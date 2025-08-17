<?php

namespace App\Http\Requests\StoreQuota;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreQuotaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('storeQuota'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'sms_credits' => ['sometimes', 'integer', 'min:0'],
            'email_credits' => ['sometimes', 'integer', 'min:0'],
            'whatsapp_credits' => ['sometimes', 'integer', 'min:0'],
            'sms_credits_expire_at' => ['sometimes', 'nullable', 'date'],
            'email_credits_expire_at' => ['sometimes', 'nullable', 'date'],
            'whatsapp_credits_expire_at' => ['sometimes', 'nullable', 'date'],
            'store_id' => ['sometimes', 'uuid'],
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
            'sms_credits.integer' => 'The SMS credits must be an integer.',
            'sms_credits.min' => 'The SMS credits must be at least 0.',
            'email_credits.integer' => 'The email credits must be an integer.',
            'email_credits.min' => 'The email credits must be at least 0.',
            'whatsapp_credits.integer' => 'The WhatsApp credits must be an integer.',
            'whatsapp_credits.min' => 'The WhatsApp credits must be at least 0.',
            'sms_credits_expire_at.date' => 'The SMS credits expiration must be a valid date.',
            'email_credits_expire_at.date' => 'The email credits expiration must be a valid date.',
            'whatsapp_credits_expire_at.date' => 'The WhatsApp credits expiration must be a valid date.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
        ];
    }
}
