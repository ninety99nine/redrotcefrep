<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Http\Resources\MediaFileResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderProductResource extends JsonResource
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
            'name' => $this->name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'is_free' => $this->is_free,
            'on_sale' => $this->on_sale,
            'has_price' => $this->has_price,
            'is_cancelled' => $this->is_cancelled,
            'order_id' => $this->order_id,
            'store_id' => $this->store_id,
            'product_id' => $this->product_id,
            'currency' => $this->currency,
            'quantity' => $this->quantity,
            'subtotal' => $this->subtotal,
            'unit_loss' => $this->unit_loss,
            'unit_price' => $this->unit_price,
            'unit_profit' => $this->unit_profit,
            'grand_total' => $this->grand_total,
            'description' => $this->description,
            'unit_weight' => $this->unit_weight,
            'unit_cost_price' => $this->unit_cost_price,
            'unit_sale_price' => $this->unit_sale_price,
            'unit_regular_price' => $this->unit_regular_price,
            'unit_sale_discount' => $this->unit_sale_discount,
            'original_quantity' => $this->original_quantity,
            'has_limited_stock' => $this->has_limited_stock,
            'sale_discount_total' => $this->sale_discount_total,
            'detected_changes' => $this->detected_changes,
            'cancellation_reasons' => $this->cancellation_reasons,
            'unit_loss_percentage' => $this->unit_loss_percentage,
            'unit_profit_percentage' => $this->unit_profit_percentage,
            'unit_sale_discount_percentage' => $this->unit_sale_discount_percentage,
            'has_exceeded_maximum_allowed_quantity_per_order' => $this->has_exceeded_maximum_allowed_quantity_per_order,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            'photo' => new MediaFileResource($this->whenLoaded('photo')),
            'order' => UserResource::collection($this->whenLoaded('order')),
            'store' => UserResource::collection($this->whenLoaded('store')),
            'product' => UserResource::collection($this->whenLoaded('product')),

            '_links' => [
                //  'show' => route('show.order.product', ['order_product' => $this->id]),
                //  'update' => route('update.order.product', ['order_product' => $this->id]),
                //  'delete' => route('delete.order.product', ['order_product' => $this->id]),
            ],
        ];
    }
}
