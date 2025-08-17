<?php

namespace App\Http\Requests\Product;

use App\Models\Product;
use App\Enums\ProductType;
use Illuminate\Support\Arr;
use App\Enums\ProductUnitType;
use Illuminate\Validation\Rule;
use App\Enums\StockQuantityType;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('updateAny', Product::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'uuid'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.id' => ['required', 'uuid'],
            'products.*.name' => ['sometimes', 'string', 'max:60'],
            'products.*.type' => ['sometimes', Rule::enum(ProductType::class)],
            'products.*.visible' => ['nullable', 'boolean'],
            'products.*.visibility_expires_at' => ['nullable', 'date'],
            'products.*.show_description' => ['nullable', 'boolean'],
            'products.*.description' => ['nullable', 'string', 'max:500'],
            'products.*.sku' => ['nullable', 'string', 'max:50'],
            'products.*.barcode' => ['nullable', 'string', 'max:50'],
            'products.*.unit_weight' => ['nullable', 'numeric', 'min:0'],
            'products.*.is_free' => ['nullable', 'boolean'],
            'products.*.is_estimated_price' => ['nullable', 'boolean'],
            'products.*.show_price_per_unit' => ['nullable', 'boolean'],
            'products.*.tax_overide' => ['nullable', 'boolean'],
            'products.*.tax_overide_amount' => ['nullable', 'numeric', 'min:0'],
            'products.*.download_link' => ['nullable', 'url'],
            'products.*.unit_type' => ['nullable', Rule::enum(ProductUnitType::class)],
            'products.*.unit_value' => ['nullable', 'numeric', 'min:0'],
            'products.*.currency' => ['nullable', 'string', 'size:3'],
            'products.*.unit_regular_price' => ['nullable', 'numeric', 'min:0'],
            'products.*.on_sale' => ['nullable', 'boolean'],
            'products.*.unit_sale_price' => ['nullable', 'numeric', 'min:0'],
            'products.*.unit_sale_discount' => ['nullable', 'numeric', 'min:0'],
            'products.*.unit_sale_discount_percentage' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'products.*.unit_cost_price' => ['nullable', 'numeric', 'min:0'],
            'products.*.has_price' => ['nullable', 'boolean'],
            'products.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'products.*.unit_profit' => ['nullable', 'numeric', 'min:0'],
            'products.*.unit_profit_percentage' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'products.*.unit_loss' => ['nullable', 'numeric', 'min:0'],
            'products.*.unit_loss_percentage' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'products.*.set_min_order_quantity' => ['sometimes', 'boolean'],
            'products.*.set_max_order_quantity' => ['sometimes', 'boolean'],
            'products.*.min_order_quantity' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'products.*.max_order_quantity' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'products.*.set_daily_capacity' => ['sometimes', 'boolean'],
            'products.*.daily_capacity' => ['nullable', 'integer', 'min:1', 'max:16777215'],
            'products.*.has_stock' => ['nullable', 'boolean'],
            'products.*.stock_quantity_type' => ['nullable', Rule::enum(StockQuantityType::class)],
            'products.*.stock_quantity' => ['nullable', 'integer', 'min:0', 'max:16777215'],
            'products.*.position' => ['nullable', 'integer', 'min:0', 'max:255'],
            'products.*.parent_product_id' => ['nullable', 'uuid'],
            'products.*.user_id' => ['nullable', 'uuid'],
            'products.*.store_id' => ['sometimes', 'uuid'],
            'products.*.data_collection_fields' => ['nullable', 'array'],
            'products.*.tags' => ['nullable', 'array'],
            'products.*.tags.*' => ['string'],
            'products.*.categories' => ['nullable', 'array'],
            'products.*.categories.*' => ['string'],
            'products.*.delivery_method_ids' => ['nullable', 'array'],
            'products.*.delivery_method_ids.*' => ['uuid'],
            'visible' => ['nullable', 'boolean'],
            'tags_to_add' => ['nullable', 'array'],
            'tags_to_add.*' => ['uuid'],
            'tags_to_remove' => ['nullable', 'array'],
            'tags_to_remove.*' => ['uuid'],
            'categories_to_add' => ['nullable', 'array'],
            'categories_to_add.*' => ['uuid'],
            'categories_to_remove' => ['nullable', 'array'],
            'categories_to_remove.*' => ['uuid'],
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
            'store_id.required' => 'The store ID is required.',
            'store_id.uuid' => 'The store ID must be a valid UUID.',
            'products.required' => 'At least one product is required.',
            'products.array' => 'The products must be an array.',
            'products.min' => 'At least one product must be provided.',
            'products.*.id.required' => 'Each product must have an ID.',
            'products.*.id.uuid' => 'Each product ID must be a valid UUID.',
            'products.*.name.string' => 'The product name must be a string.',
            'products.*.name.max' => 'The product name must not exceed 60 characters.',
            'products.*.type.enum' => 'The product type must be one of: ' . Arr::join(ProductType::values(), ', ', ' or '),
            'products.*.sku.max' => 'The SKU must not exceed 50 characters.',
            'products.*.barcode.max' => 'The barcode must not exceed 50 characters.',
            'products.*.description.max' => 'The description must not exceed 500 characters.',
            'products.*.unit_weight.numeric' => 'The unit weight must be a number.',
            'products.*.unit_weight.min' => 'The unit weight must be at least 0.',
            'products.*.unit_regular_price.numeric' => 'The regular price must be a number.',
            'products.*.unit_regular_price.min' => 'The regular price must be at least 0.',
            'products.*.unit_sale_price.numeric' => 'The sale price must be a number.',
            'products.*.unit_sale_price.min' => 'The sale price must be at least 0.',
            'products.*.unit_sale_discount.numeric' => 'The sale discount must be a number.',
            'products.*.unit_sale_discount.min' => 'The sale discount must be at least 0.',
            'products.*.unit_sale_discount_percentage.integer' => 'The sale discount percentage must be an integer.',
            'products.*.unit_sale_discount_percentage.min' => 'The sale discount percentage must be at least 0.',
            'products.*.unit_sale_discount_percentage.max' => 'The sale discount percentage must not exceed 65535.',
            'products.*.unit_cost_price.numeric' => 'The cost price must be a number.',
            'products.*.unit_cost_price.min' => 'The cost price must be at least 0.',
            'products.*.unit_price.numeric' => 'The unit price must be a number.',
            'products.*.unit_price.min' => 'The unit price must be at least 0.',
            'products.*.unit_profit.numeric' => 'The unit profit must be a number.',
            'products.*.unit_profit.min' => 'The unit profit must be at least 0.',
            'products.*.unit_profit_percentage.integer' => 'The unit profit percentage must be an integer.',
            'products.*.unit_profit_percentage.min' => 'The unit profit percentage must be at least 0.',
            'products.*.unit_profit_percentage.max' => 'The unit profit percentage must not exceed 65535.',
            'products.*.unit_loss.numeric' => 'The unit loss must be a number.',
            'products.*.unit_loss.min' => 'The unit loss must be at least 0.',
            'products.*.unit_loss_percentage.integer' => 'The unit loss percentage must be an integer.',
            'products.*.unit_loss_percentage.min' => 'The unit loss percentage must be at least 0.',
            'products.*.unit_loss_percentage.max' => 'The unit loss percentage must not exceed 65535.',
            'products.*.min_order_quantity.integer' => 'The minimum order quantity must be an integer.',
            'products.*.min_order_quantity.min' => 'The minimum order quantity must be at least 1.',
            'products.*.min_order_quantity.max' => 'The minimum order quantity must not exceed 65535.',
            'products.*.max_order_quantity.integer' => 'The maximum order quantity must be an integer.',
            'products.*.max_order_quantity.min' => 'The maximum order quantity must be at least 1.',
            'products.*.max_order_quantity.max' => 'The maximum order quantity must not exceed 65535.',
            'products.*.set_daily_capacity.boolean' => 'The set daily capacity must be a boolean.',
            'products.*.daily_capacity.integer' => 'The daily capacity must be an integer.',
            'products.*.daily_capacity.min' => 'The daily capacity must be at least 1.',
            'products.*.daily_capacity.max' => 'The daily capacity must not exceed 16777215.',
            'products.*.stock_quantity.integer' => 'The stock quantity must be an integer.',
            'products.*.stock_quantity.min' => 'The stock quantity must be at least 0.',
            'products.*.stock_quantity.max' => 'The stock quantity must not exceed 16777215.',
            'products.*.position.integer' => 'The position must be an integer.',
            'products.*.position.min' => 'The position must be at least 0.',
            'products.*.position.max' => 'The position must not exceed 255.',
            'products.*.parent_product_id.uuid' => 'The parent product ID must be a valid UUID.',
            'products.*.user_id.uuid' => 'The user ID must be a valid UUID.',
            'products.*.store_id.uuid' => 'The store ID must be a valid UUID.',
            'products.*.set_min_order_quantity.boolean' => 'The minimum order quantity must be a boolean.',
            'products.*.set_max_order_quantity.boolean' => 'The maximum order quantity must be a boolean.',
            'products.*.stock_quantity_type.enum' => 'The stock quantity type must be one of: ' . Arr::join(StockQuantityType::values(), ', ', ' or '),
            'products.*.data_collection_fields.array' => 'The data collection fields must be an array.',
            'products.*.tags.array' => 'The tags must be an array.',
            'products.*.tags.*.string' => 'Each product tag must be a string.',
            'products.*.categories.array' => 'The categories must be an array.',
            'products.*.categories.*.string' => 'Each product category must be a string.',
            'products.*.download_link.url' => 'The download link must be a valid URL.',
            'products.*.unit_type.enum' => 'The unit type must be one of: ' . Arr::join(ProductUnitType::values(), ', ', ' or '),
            'products.*.unit_value.numeric' => 'The unit value must be a number.',
            'products.*.unit_value.min' => 'The unit value must be at least 0.',
            'products.*.tax_overide_amount.numeric' => 'The tax override amount must be a number.',
            'products.*.tax_overide_amount.min' => 'The tax override amount must be at least 0.',
            'products.*.delivery_method_ids.array' => 'The delivery method IDs must be an array.',
            'products.*.delivery_method_ids.*.uuid' => 'Each delivery method ID must be a valid UUID.',
            'visible.boolean' => 'The visible field must be a boolean.',
            'tags_to_add.array' => 'The tags to add must be an array.',
            'tags_to_add.*.uuid' => 'Each tag ID to add must be a valid UUID.',
            'tags_to_remove.array' => 'The tags to remove must be an array.',
            'tags_to_remove.*.uuid' => 'Each tag ID to remove must be a valid UUID.',
            'categories_to_add.array' => 'The categories to add must be an array.',
            'categories_to_add.*.uuid' => 'Each category ID to add must be a valid UUID.',
            'categories_to_remove.array' => 'The categories to remove must be an array.',
            'categories_to_remove.*.uuid' => 'Each category ID to remove must be a valid UUID.',
        ];
    }
}
