<?php

namespace App\Http\Requests\StoreQuota;

use App\Models\StoreQuota;
use Illuminate\Foundation\Http\FormRequest;

class CreateStoreQuotaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', StoreQuota::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'sms_credits' => ['nullable', 'integer', 'min:0'],
            'email_credits' => ['nullable', 'integer', 'min:0'],
            'whatsapp_credits' => ['nullable', 'integer', 'min:0'],
            'sms_credits_expire_at' => ['nullable', 'date'],
            'email_credits_expire_at' => ['nullable', 'date'],
            'whatsapp_credits_expire_at' => ['nullable', 'date'],
            'store_id' => ['required', 'uuid'],
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
            'store_id.required' => 'The store ID is required.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
        ];
    }
}
