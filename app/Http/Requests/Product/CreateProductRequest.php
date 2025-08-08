<?php

namespace App\Http\Requests\Product;

use App\Models\Product;
use App\Enums\ProductType;
use Illuminate\Support\Arr;
use App\Enums\ProductUnitType;
use Illuminate\Validation\Rule;
use App\Enums\StockQuantityType;
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
            'type' => ['nullable', Rule::enum(ProductType::class)],
            'visible' => ['nullable', 'boolean'],
            'visibility_expires_at' => ['nullable', 'date'],
            'show_description' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:500'],
            'sku' => ['nullable', 'string', 'max:50'],
            'barcode' => ['nullable', 'string', 'max:50'],
            'unit_weight' => ['nullable', 'numeric', 'min:0'],
            'is_free' => ['nullable', 'boolean'],
            'is_estimated_price' => ['nullable', 'boolean'],
            'show_price_per_unit' => ['nullable', 'boolean'],
            'tax_overide' => ['nullable', 'boolean'],
            'tax_overide_amount' => ['nullable', 'numeric', 'min:0'],
            'download_link' => ['nullable', 'url'],
            'unit_type' => ['nullable', Rule::enum(ProductUnitType::class)],
            'unit_value' => ['nullable', 'numeric', 'min:0'],
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
            'set_min_order_quantity' => ['sometimes', 'boolean'],
            'set_max_order_quantity' => ['sometimes', 'boolean'],
            'min_order_quantity' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'max_order_quantity' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'set_daily_capacity' => ['sometimes', 'boolean'],
            'daily_capacity' => ['nullable', 'integer', 'min:1', 'max:16777215'],
            'has_stock' => ['nullable', 'boolean'],
            'stock_quantity_type' => ['nullable', Rule::enum(StockQuantityType::class)],
            'stock_quantity' => ['nullable', 'integer', 'min:0', 'max:16777215'],
            'position' => ['nullable', 'integer', 'min:0', 'max:255'],
            'parent_product_id' => ['nullable', 'uuid'],
            'user_id' => ['nullable', 'uuid'],
            'store_id' => ['required', 'uuid'],
            'photo' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:5120'],
            'data_collection_fields' => ['nullable', 'array'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['string'],
            'delivery_method_ids' => ['nullable', 'array'],
            'delivery_method_ids.*' => ['uuid'],
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
            'type.enum' => 'The product type must be one of: ' . Arr::join(ProductType::values(), ', ', ' or '),
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
            'min_order_quantity.integer' => 'The minimum order quantity must be an integer.',
            'min_order_quantity.min' => 'The minimum order quantity must be at least 1.',
            'min_order_quantity.max' => 'The minimum order quantity must not exceed 65535.',
            'max_order_quantity.integer' => 'The maximum order quantity must be an integer.',
            'max_order_quantity.min' => 'The maximum order quantity must be at least 1.',
            'max_order_quantity.max' => 'The maximum order quantity must not exceed 65535.',
            'set_daily_capacity.boolean' => 'The set daily capacity must be a boolean.',
            'daily_capacity.integer' => 'The daily capacity must be an integer.',
            'daily_capacity.min' => 'The daily capacity must be at least 1.',
            'daily_capacity.max' => 'The daily capacity must not exceed 16777215.',
            'stock_quantity.integer' => 'The stock quantity must be an integer.',
            'stock_quantity.min' => 'The stock quantity must be at least 0.',
            'stock_quantity.max' => 'The stock quantity must not exceed 16777215.',
            'position.integer' => 'The position must be an integer.',
            'position.min' => 'The position must be at least 0.',
            'position.max' => 'The position must not exceed 255.',
            'parent_product_id.uuid' => 'The parent product ID must be a valid UUID.',
            'user_id.uuid' => 'The user ID must be a valid UUID.',
            'store_id.required' => 'The store ID is required.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'set_min_order_quantity.boolean' => 'The minimum order quantity must be a boolean.',
            'set_max_order_quantity.boolean' => 'The maximum order quantity must be a boolean.',
            'stock_quantity_type.enum' => 'The stock quantity type must be one of: ' . Arr::join(StockQuantityType::values(), ', ', ' or '),
            'photo.file' => 'The photo must be a valid file.',
            'photo.mimes' => 'The photo must be a JPEG, PNG, JPG, GIF, or SVG.',
            'photo.max' => 'The photo size must not exceed 5MB.',
            'data_collection_fields.array' => 'The data collection fields must be an array.',
            'tags.array' => 'The tags must be an array.',
            'tags.string' => 'The product tags must be a string.',
            'categories.array' => 'The categories must be an array.',
            'categories.string' => 'The product categories must be a string.',
            'download_link.url' => 'The download link must be a valid URL.',
            'unit_type.enum' => 'The unit type must be one of: ' . Arr::join(ProductUnitType::values(), ', ', ' or '),
            'unit_value.numeric' => 'The unit value must be a number.',
            'unit_value.min' => 'The unit value must be at least 0.',
            'tax_overide_amount.numeric' => 'The tax override amount must be a number.',
            'tax_overide_amount.min' => 'The tax override amount must be at least 0.',
            'delivery_method_ids.array' => 'The delivery method IDs must be an array.',
            'delivery_method_ids.*.uuid' => 'Each delivery method ID must be a valid UUID.',
        ];
    }
}
