<?php

namespace App\Http\Requests\Product;

use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use App\Enums\StockQuantityType;
use App\Enums\AllowedQuantityPerOrder;
use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Product::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            'visible' => ['nullable', 'boolean'],
            'visibility_expires_at' => ['nullable', 'date'],
            'show_description' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:500'],
            'sku' => ['nullable', 'string', 'max:50', 'unique:products,sku'],
            'barcode' => ['nullable', 'string', 'max:50'],
            'allow_variations' => ['nullable', 'boolean'],
            'variant_attributes' => ['nullable', 'array'],
            'variant_attributes.*' => ['array'],
            'total_variations' => ['nullable', 'integer', 'min:0', 'max:255'],
            'total_visible_variations' => ['nullable', 'integer', 'min:0', 'max:255'],
            'unit_weight' => ['nullable', 'numeric', 'min:0'],
            'is_free' => ['nullable', 'boolean'],
            'currency' => ['nullable', 'string', 'size:3'],
            'unit_regular_price' => ['nullable', 'numeric', 'min:0'],
            'on_sale' => ['nullable', 'boolean'],
            'unit_sale_price' => ['nullable', 'numeric', 'min:0'],
            'unit_sale_discount' => ['nullable', 'numeric', 'min:0'],
            'unit_sale_discount_percentage' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'unit_cost_price' => ['nullable', 'numeric', 'min:0'],
            'has_price' => ['nullable', 'boolean'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'unit_profit' => ['nullable', 'numeric', 'min:0'],
            'unit_profit_percentage' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'unit_loss' => ['nullable', 'numeric', 'min:0'],
            'unit_loss_percentage' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'allowed_quantity_per_order' => ['nullable', Rule::enum(AllowedQuantityPerOrder::class)],
            'maximum_allowed_quantity_per_order' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'has_stock' => ['nullable', 'boolean'],
            'stock_quantity_type' => ['nullable', Rule::enum(StockQuantityType::class)],
            'stock_quantity' => ['nullable', 'integer', 'min:0', 'max:16777215'],
            'position' => ['nullable', 'integer', 'min:0', 'max:255'],
            'parent_product_id' => ['nullable', 'uuid', 'exists:products,id'],
            'user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'store_id' => ['required', 'uuid', 'exists:stores,id'],
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
            'name.required' => 'The product name is required.',
            'name.string' => 'The product name must be a string.',
            'name.max' => 'The product name must not exceed 60 characters.',
            'sku.unique' => 'This SKU is already in use.',
            'sku.max' => 'The SKU must not exceed 50 characters.',
            'barcode.max' => 'The barcode must not exceed 50 characters.',
            'description.max' => 'The description must not exceed 500 characters.',
            'unit_weight.numeric' => 'The unit weight must be a number.',
            'unit_weight.min' => 'The unit weight must be at least 0.',
            'unit_regular_price.numeric' => 'The regular price must be a number.',
            'unit_regular_price.min' => 'The regular price must be at least 0.',
            'unit_sale_price.numeric' => 'The sale price must be a number.',
            'unit_sale_price.min' => 'The sale price must be at least 0.',
            'unit_sale_discount.numeric' => 'The sale discount must be a number.',
            'unit_sale_discount.min' => 'The sale discount must be at least 0.',
            'unit_sale_discount_percentage.integer' => 'The sale discount percentage must be an integer.',
            'unit_sale_discount_percentage.min' => 'The sale discount percentage must be at least 0.',
            'unit_sale_discount_percentage.max' => 'The sale discount percentage must not exceed 65535.',
            'unit_cost_price.numeric' => 'The cost price must be a number.',
            'unit_cost_price.min' => 'The cost price must be at least 0.',
            'unit_price.numeric' => 'The unit price must be a number.',
            'unit_price.min' => 'The unit price must be at least 0.',
            'unit_profit.numeric' => 'The unit profit must be a number.',
            'unit_profit.min' => 'The unit profit must be at least 0.',
            'unit_profit_percentage.integer' => 'The unit profit percentage must be an integer.',
            'unit_profit_percentage.min' => 'The unit profit percentage must be at least 0.',
            'unit_profit_percentage.max' => 'The unit profit percentage must not exceed 65535.',
            'unit_loss.numeric' => 'The unit loss must be a number.',
            'unit_loss.min' => 'The unit loss must be at least 0.',
            'unit_loss_percentage.integer' => 'The unit loss percentage must be an integer.',
            'unit_loss_percentage.min' => 'The unit loss percentage must be at least 0.',
            'unit_loss_percentage.max' => 'The unit loss percentage must not exceed 65535.',
            'total_variations.integer' => 'The total variations must be an integer.',
            'total_variations.min' => 'The total variations must be at least 0.',
            'total_variations.max' => 'The total variations must not exceed 255.',
            'total_visible_variations.integer' => 'The total visible variations must be an integer.',
            'total_visible_variations.min' => 'The total visible variations must be at least 0.',
            'total_visible_variations.max' => 'The total visible variations must not exceed 255.',
            'maximum_allowed_quantity_per_order.integer' => 'The maximum allowed quantity per order must be an integer.',
            'maximum_allowed_quantity_per_order.min' => 'The maximum allowed quantity per order must be at least 1.',
            'maximum_allowed_quantity_per_order.max' => 'The maximum allowed quantity per order must not exceed 65535.',
            'stock_quantity.integer' => 'The stock quantity must be an integer.',
            'stock_quantity.min' => 'The stock quantity must be at least 0.',
            'stock_quantity.max' => 'The stock quantity must not exceed 16777215.',
            'position.integer' => 'The position must be an integer.',
            'position.min' => 'The position must be at least 0.',
            'position.max' => 'The position must not exceed 255.',
            'parent_product_id.uuid' => 'The parent product ID must be a valid UUID.',
            'parent_product_id.exists' => 'The specified parent product does not exist.',
            'user_id.uuid' => 'The user ID must be a valid UUID.',
            'user_id.exists' => 'The specified user does not exist.',
            'store_id.required' => 'The store ID is required.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'store_id.exists' => 'The specified store does not exist.',
            'variant_attributes.array' => 'The variant attributes must be an array.',
            'allowed_quantity_per_order.enum' => 'The allowed quantity per order must be one of: ' . Arr::join(AllowedQuantityPerOrder::values(), ',', 'or'),
            'stock_quantity_type.enum' => 'The stock quantity type must be one of: ' . Arr::join(StockQuantityType::values(), ',', 'or'),
            'photo.file' => 'The photo must be a valid file.',
            'photo.mimes' => 'The photo must be a JPEG, PNG, JPG, GIF, or SVG.',
            'photo.max' => 'The photo size must not exceed 5MB.',
        ];
    }
}
