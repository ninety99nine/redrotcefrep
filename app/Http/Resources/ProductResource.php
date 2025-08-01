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
            'position' => $this->position,
            'user_id' => $this->user_id,
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
            'allow_variations' => $this->allow_variations,
            'total_variations' => $this->total_variations,
            'parent_product_id' => $this->parent_product_id,
            'unit_regular_price' => $this->unit_regular_price,
            'variant_attributes' => $this->variant_attributes,
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
            'total_visible_variations' => $this->total_visible_variations,
            'unit_sale_discount_percentage' => $this->unit_sale_discount_percentage,
            'visibility_expires_at' => $this->visibility_expires_at ? $this->visibility_expires_at->toDateTimeString() : null,

            'user' => new UserResource($this->whenLoaded('user')),
            'store' => new StoreResource($this->whenLoaded('store')),
            'photo' => new MediaFileResource($this->whenLoaded('photo')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'photos' => MediaFileResource::collection($this->whenLoaded('photos')),
            'parent_product' => new ProductResource($this->whenLoaded('parentProduct')),
            'variations' => ProductResource::collection($this->whenLoaded('variations')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'media_files' => MediaFileResource::collection($this->whenLoaded('mediaFiles')),

            '_links' => [
                'show' => route('show.product', ['product' => $this->id]),
                'update' => route('update.product', ['product' => $this->id]),
                'delete' => route('delete.product', ['product' => $this->id]),
            ],
        ];
    }
}
