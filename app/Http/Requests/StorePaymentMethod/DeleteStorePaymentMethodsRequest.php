<?php

namespace App\Http\Requests\StorePaymentMethod;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\StorePaymentMethod;
class DeleteStorePaymentMethodsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', StorePaymentMethod::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'uuid', 'exists:stores,id'],
            'store_payment_method_ids' => ['required', 'array'],
            'store_payment_method_ids.*' => ['uuid', 'exists:store_payment_method,id'],
        ];
    }
}
