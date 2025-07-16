<?php

namespace App\Http\Requests\MediaFile;

use App\Models\MediaFile;
use Illuminate\Support\Arr;
use App\Enums\UploadFolderName;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CreateMediaFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', MediaFile::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(UploadFolderName::class)->except(UploadFolderName::QR_CODES)],
            'file' => ['required', 'file', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:5120'],
            'store_id' => ['required_if:type,'.UploadFolderName::STORE_LOGO->value, 'uuid', 'exists:stores,id'],
            'user_id' => ['required_if:type,'.UploadFolderName::PROFILE_PHOTO->value, 'uuid', 'exists:users,id'],
            'product_id' => ['required_if:type,'.UploadFolderName::PRODUCT_PHOTO->value, 'uuid', 'exists:products,id'],
            'store_payment_method_id' => [
                'required_if:type,' . implode(',', [
                    UploadFolderName::STORE_PAYMENT_METHOD_LOGO->value,
                    UploadFolderName::STORE_PAYMENT_METHOD_PHOTO->value
                ]),
                'uuid', 'exists:store_payment_method,id'
            ],
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
            'type.required' => 'The file type is required.',
            'type.enum' => 'The file type must be one of: ' . Arr::join(UploadFolderName::values(), ',', 'or'),
            'file.required' => 'The file is required.',
            'file.file' => 'The file must be a valid file.',
            'file.mimes' => 'The file must be a JPEG, PNG, JPG, GIF, or SVG.',
            'file.max' => 'The file size must not exceed 5MB.',
            'store_id.required_if' => 'The store ID is required when type is store_logo.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'store_id.exists' => 'The specified store does not exist.',
            'user_id.required_if' => 'The user ID is required when type is profile_photo.',
            'user_id.uuid' => 'The user ID must be a valid UUID.',
            'user_id.exists' => 'The specified user does not exist.',
            'product_id.required_if' => 'The product ID is required when type is product_photo.',
            'product_id.uuid' => 'The product ID must be a valid UUID.',
            'product_id.exists' => 'The specified product does not exist.',
            'store_payment_method_id.required_if' => 'The store payment method ID is required when type is store_payment_method_logo or store_payment_method_photo.',
            'store_payment_method_id.uuid' => 'The store payment method ID must be a valid UUID.',
            'store_payment_method_id.exists' => 'The specified store payment method does not exist.',
        ];
    }
}
