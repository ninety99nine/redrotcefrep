<?php

namespace App\Http\Requests\DesignCard;

use App\Models\DesignCard;
use Illuminate\Support\Arr;
use App\Enums\DesignCardType;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CreateDesignCardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', DesignCard::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'visible' => ['nullable', 'boolean'],
            'type' => ['required', Rule::enum(DesignCardType::class)],
            'metadata' => ['required', 'array'],
            'position' => ['nullable', 'integer', 'min:0', 'max:255'],
            'store_id' => ['required', 'uuid'],
            'photo' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:5120'],
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
            'type.required' => 'The type is required.',
            'type.enum' => 'The type must be one of: ' . Arr::join(DesignCardType::values(), ', ', ' or '),
            'metadata.required' => 'The metadata is required.',
            'metadata.array' => 'The metadata must be an array.',
            'position.integer' => 'The position must be an integer.',
            'position.min' => 'The position must be at least 0.',
            'position.max' => 'The position must not exceed 255.',
            'store_id.required' => 'The store ID is required.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'photo.file' => 'The photo must be a valid file.',
            'photo.mimes' => 'The photo must be a JPEG, PNG, JPG, GIF, or SVG.',
            'photo.max' => 'The photo size must not exceed 5MB.',
        ];
    }
}
