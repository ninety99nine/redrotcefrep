<?php

namespace App\Http\Requests\StorePaymentMethod;

use App\Models\StorePaymentMethod;
use Illuminate\Foundation\Http\FormRequest;

class ShowStorePaymentMethodsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', StorePaymentMethod::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'uuid', 'exists:stores,id']
        ];
    }
}
