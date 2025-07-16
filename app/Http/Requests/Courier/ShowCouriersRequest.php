<?php

namespace App\Http\Requests\Courier;

use App\Models\Courier;
use App\Enums\Association;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowCouriersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Courier::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'courier_id' => ['sometimes', 'uuid', 'exists:couriers,id'],
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
            'courier_id.uuid' => 'The courier ID must be a valid UUID.',
            'courier_id.exists' => 'The specified courier does not exist.',
        ];
    }
}
