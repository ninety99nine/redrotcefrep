<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'type' => $this->type,
            'barcode' => $this->barcode,
            'visible' => $this->visible,
            'is_free' => $this->is_free,
            'on_sale' => $this->on_sale,
            'user_id' => $this->user_id,
            'position' => $this->position,
            'store_id' => $this->store_id,
            'currency' => $this->currency,
            'has_price' => $this->has_price,
            'has_stock' => $this->has_stock,
            'unit_loss' => $this->unit_loss,
            'unit_price' => $this->unit_price,
            'unit_profit' => $this->unit_profit,
            'description' => $this->description,
            'stock_quantity' => $this->stock_quantity ? (string)$this->stock_quantity : null,
            'unit_sale_price' => $this->unit_sale_price,
            'unit_cost_price' => $this->unit_cost_price,
            'unit_weight' => $this->unit_weight ? (string)$this->unit_weight : null,
            'show_description' => $this->show_description,
            'parent_product_id' => $this->parent_product_id,
            'unit_regular_price' => $this->unit_regular_price,
            'unit_sale_discount' => $this->unit_sale_discount,
            'tax_overide' => $this->tax_overide,
            'tax_overide_amount' => $this->tax_overide_amount,
            'download_link' => $this->download_link,
            'unit_type' => $this->unit_type,
            'unit_value' => $this->unit_value ? (string)$this->unit_value : null,
            'is_estimated_price' => $this->is_estimated_price,
            'show_price_per_unit' => $this->show_price_per_unit,
            'set_daily_capacity' => $this->set_daily_capacity,
            'daily_capacity' => $this->daily_capacity ? (string)$this->daily_capacity : null,
            'stock_quantity_type' => $this->stock_quantity_type,
            'set_min_order_quantity' => $this->set_min_order_quantity,
            'min_order_quantity' => $this->min_order_quantity ? (string)$this->min_order_quantity : null,
            'set_max_order_quantity' => $this->set_max_order_quantity,
            'max_order_quantity' => $this->max_order_quantity ? (string)$this->max_order_quantity : null,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'unit_loss_percentage' => $this->unit_loss_percentage,
            'unit_profit_percentage' => $this->unit_profit_percentage,
            'unit_sale_discount_percentage' => $this->unit_sale_discount_percentage,
            'visibility_expires_at' => $this->visibility_expires_at ? $this->visibility_expires_at->toDateTimeString() : null,
            'data_collection_fields' => $this->data_collection_fields,

            'user' => UserResource::make($this->whenLoaded('user')),
            'store' => StoreResource::make($this->whenLoaded('store')),
            'photo' => MediaFileResource::make($this->whenLoaded('photo')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'variant' => ProductResource::make($this->whenLoaded('variant')),
            'photos' => MediaFileResource::collection($this->whenLoaded('photos')),
            'variants' => ProductResource::collection($this->whenLoaded('variants')),
            'parent_product' => ProductResource::make($this->whenLoaded('parentProduct')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'media_files' => MediaFileResource::collection($this->whenLoaded('mediaFiles')),
            'delivery_methods' => DeliveryMethodResource::collection($this->whenLoaded('deliveryMethods')),

            'variants_count' => $this->whenCounted('variants'),
        ];
    }
}
