<?php

namespace App\Http\Requests\User;

use App\Models\User;
use App\Enums\Association;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowUsersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', User::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'store_id' => ['required_without:association', 'uuid', 'exists:stores,id'],
            'association' => ['sometimes', Rule::enum(Association::class)->only([Association::SUPER_ADMIN])]
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
            'user_id.uuid' => 'The user ID must be a valid UUID.',
            'user_id.exists' => 'The specified user does not exist.',
            'association.enum' => 'The association must be one of: ' . Arr::join([Association::SUPER_ADMIN->value], ',', 'or'),
        ];
    }
}
