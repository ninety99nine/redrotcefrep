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
            'stock_quantity' => $this->stock_quantity,
            'unit_sale_price' => $this->unit_sale_price,
            'unit_cost_price' => $this->unit_cost_price,
            'unit_weight' => $this->unit_weight,
            'show_description' => $this->show_description,
            'allow_variations' => $this->allow_variations,
            'total_variations' => $this->total_variations,
            'parent_product_id' => $this->parent_product_id,
            'unit_regular_price' => $this->unit_regular_price,
            'variant_attributes' => $this->variant_attributes,
            'unit_sale_discount' => $this->unit_sale_discount,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'unit_loss_percentage' => $this->unit_loss_percentage,
            'unit_profit_percentage' => $this->unit_profit_percentage,
            'stock_quantity_type' => $this->stock_quantity_type,
            'total_visible_variations' => $this->total_visible_variations,
            'unit_sale_discount_percentage' => $this->unit_sale_discount_percentage,
            'allowed_quantity_per_order' => $this->allowed_quantity_per_order,
            'maximum_allowed_quantity_per_order' => $this->maximum_allowed_quantity_per_order,
            'visibility_expires_at' => $this->visibility_expires_at ? $this->visibility_expires_at->toDateTimeString() : null,

            'user' => new UserResource($this->whenLoaded('user')),
            'store' => new StoreResource($this->whenLoaded('store')),
            'photo' => new MediaFileResource($this->whenLoaded('photo')),
            'photos' => MediaFileResource::collection($this->whenLoaded('photos')),
            'variations' => UserResource::collection($this->whenLoaded('variations')),
            'parent_product' => new ProductResource($this->whenLoaded('parentProduct')),
            'media_files' => MediaFileResource::collection($this->whenLoaded('mediaFiles')),

            '_links' => [
                'show' => route('show.product', ['product' => $this->id]),
                'update' => route('update.product', ['product' => $this->id]),
                'delete' => route('delete.product', ['product' => $this->id]),
            ],
        ];
    }
}
