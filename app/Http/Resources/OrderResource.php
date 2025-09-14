<?php

namespace App\Http\Resources;

use App\Http\Resources\StoreResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\CourierResource;
use App\Http\Resources\DeliveryMethodResource;
use App\Services\PhoneNumberService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'number' => $this->number,
            'summary' => $this->summary,
            'status' => $this->status,
            'currency' => $this->currency,
            'subtotal' => $this->subtotal,
            'discount_total' => $this->discount_total,
            'subtotal_after_discount' => $this->subtotal_after_discount,
            'vat_method' => $this->vat_method,
            'vat_rate' => $this->vat_rate,
            'vat_amount' => $this->vat_amount,
            'fee_total' => $this->fee_total,
            'adjustment_total' => $this->adjustment_total,
            'grand_total' => $this->grand_total,
            'payment_status' => $this->payment_status,
            'paid_total' => $this->paid_total,
            'paid_percentage' => $this->paid_percentage,
            'outstanding_total' => $this->outstanding_total,
            'outstanding_percentage' => $this->outstanding_percentage,
            'total_products' => $this->total_products,
            'total_cancelled_products' => $this->total_cancelled_products,
            'total_uncancelled_products' => $this->total_uncancelled_products,
            'total_product_quantities' => $this->total_product_quantities,
            'total_cancelled_product_quantities' => $this->total_cancelled_product_quantities,
            'total_uncancelled_product_quantities' => $this->total_uncancelled_product_quantities,
            'total_promotions' => $this->total_promotions,
            'total_cancelled_promotions' => $this->total_cancelled_promotions,
            'total_uncancelled_promotions' => $this->total_uncancelled_promotions,
            'applied_promotion_code' => $this->applied_promotion_code,
            'delivery_method_name' => $this->delivery_method_name,
            'free_delivery' => $this->free_delivery,
            'delivery_date' => $this->delivery_date ? $this->delivery_date->toDateString() : null,
            'delivery_timeslot' => $this->delivery_timeslot,
            'delivery_method_id' => $this->delivery_method_id,
            'delivery_distance_value' => $this->delivery_distance_value,
            'delivery_distance_unit' => $this->delivery_distance_unit,
            'delivery_distance_text' => $this->delivery_distance_text,
            'delivery_duration_value' => $this->delivery_duration_value,
            'delivery_duration_text' => $this->delivery_duration_text,
            'delivery_weight_value' => $this->delivery_weight_value,
            'delivery_weight_unit' => $this->delivery_weight_unit,
            'delivery_weight_text' => $this->delivery_weight_text,
            'courier_id' => $this->courier_id,
            'tracking_number' => $this->tracking_number,
            'collection_code' => $this->collection_code,
            'collection_qr_code' => $this->collection_qr_code,
            'collection_code_expires_at' => $this->collection_code_expires_at ? $this->collection_code_expires_at->toDateTimeString() : null,
            'collection_verified' => $this->collection_verified,
            'collection_verified_at' => $this->collection_verified_at ? $this->collection_verified_at->toDateTimeString() : null,
            'collection_verified_by_user_id' => $this->collection_verified_by_user_id,
            'collection_note' => $this->collection_note,
            'cancellation_reason' => $this->cancellation_reason,
            'cancelled_at' => $this->cancelled_at ? $this->cancelled_at->toDateTimeString() : null,
            'customer_name' => $this->customer_name,
            'customer_first_name' => $this->customer_first_name,
            'customer_last_name' => $this->customer_last_name,
            'customer_mobile_number' => $this->customer_mobile_number ? PhoneNumberService::formatPhoneNumber($this->customer_mobile_number) : null,
            'customer_email' => $this->customer_email,
            'customer_note' => $this->customer_note,
            'customer_id' => $this->customer_id,
            'placed_by_user_id' => $this->placed_by_user_id,
            'total_views_by_team' => $this->total_views_by_team,
            'first_viewed_by_team_at' => $this->first_viewed_by_team_at ? $this->first_viewed_by_team_at->toDateTimeString() : null,
            'last_viewed_by_team_at' => $this->last_viewed_by_team_at ? $this->last_viewed_by_team_at->toDateTimeString() : null,
            'internal_note' => $this->internal_note,
            'remark' => $this->remark,
            'store_id' => $this->store_id,
            'created_by_user_id' => $this->created_by_user_id,
            'assigned_to_user_id' => $this->assigned_to_user_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            'store' => StoreResource::make($this->whenLoaded('store')),
            'courier' => CourierResource::make($this->whenLoaded('courier')),
            'customer' => CustomerResource::make($this->whenLoaded('customer')),
            'placed_by_user' => UserResource::make($this->whenLoaded('placedByUser')),
            'created_by_user' => UserResource::make($this->whenLoaded('createdByUser')),
            'assigned_to_user' => UserResource::make($this->whenLoaded('assignedToUser')),
            'delivery_method' => DeliveryMethodResource::make($this->whenLoaded('deliveryMethod')),
            'delivery_address' => DeliveryAddressResource::make($this->whenLoaded('deliveryAddress')),
            'collection_verified_by_user' => UserResource::make($this->whenLoaded('collectionVerifiedByUser')),

            'order_fees' => OrderFeeResource::collection($this->whenLoaded('orderFees')),
            'order_comments' => OrderCommentResource::collection($this->whenLoaded('orderComments')),
            'order_products' => OrderProductResource::collection($this->whenLoaded('orderProducts')),
            'order_discounts' => OrderDiscountResource::collection($this->whenLoaded('orderDiscounts')),
            'order_promotions' => OrderPromotionResource::collection($this->whenLoaded('orderPromotions')),
        ];
    }
}
