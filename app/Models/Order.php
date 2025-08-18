<?php

namespace App\Models;

use App\Casts\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast;

class Order extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected function casts(): array
    {
        return [

            'vat_rate' => 'float',

            'delivery_date' => 'date',

            'free_delivery' => 'boolean',
            'collection_verified' => 'boolean',
            'applied_promotion_code' => 'boolean',

            'cancelled_at' => 'datetime',
            'collection_verified_at' => 'datetime',
            'last_viewed_by_team_at' => 'datetime',
            'first_viewed_by_team_at' => 'datetime',
            'collection_code_expires_at' => 'datetime',

            'total_products' => 'integer',
            'paid_percentage' => 'integer',
            'total_promotions' => 'integer',
            'pending_percentage' => 'integer',
            'outstanding_percentage' => 'integer',
            'total_product_quantities' => 'integer',
            'total_cancelled_products' => 'integer',
            'total_uncancelled_products' => 'integer',
            'total_cancelled_promotions' => 'integer',
            'total_uncancelled_promotions' => 'integer',
            'total_cancelled_product_quantities' => 'integer',
            'total_uncancelled_product_quantities' => 'integer',

            'subtotal' => Money::class,
            'fee_total' => Money::class,
            'vat_amount' => Money::class,
            'paid_total' => Money::class,
            'grand_total' => Money::class,
            'pending_total' => Money::class,
            'discount_total' => Money::class,
            'adjustment_total' => Money::class,
            'outstanding_total' => Money::class,
            'subtotal_after_discount' => Money::class,

            'customer_mobile_number' => E164PhoneNumberCast::class
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number', 'summary','status','currency','subtotal','discount_total','subtotal_after_discount','vat_method','vat_rate',
        'vat_amount','fee_total','adjustment_total','grand_total','payment_status','paid_total','paid_percentage',
        'pending_total','pending_percentage','outstanding_total','outstanding_percentage','total_products',
        'total_cancelled_products','total_uncancelled_products','total_product_quantities','total_cancelled_product_quantities',
        'total_uncancelled_product_quantities','total_promotions','total_cancelled_promotions','total_uncancelled_promotions',
        'applied_promotion_code','delivery_method_name','free_delivery','delivery_date','delivery_timeslot','delivery_method_id',
        'delivery_distance_value','delivery_distance_unit','delivery_distance_text','delivery_duration_value','delivery_duration_text',
        'delivery_weight_value','delivery_weight_unit','delivery_weight_text','courier_id','tracking_number','collection_code',
        'collection_qr_code','collection_code_expires_at','collection_verified','collection_verified_at','collection_verified_by_user_id',
        'collection_note','cancellation_reason','cancelled_at','customer_first_name','customer_last_name','customer_mobile_number',
        'customer_email','customer_note','customer_id','placed_by_user_id','total_views_by_team','first_viewed_by_team_at',
        'last_viewed_by_team_at','internal_note','remark','store_id','created_by_user_id','assigned_to_user_id',
    ];

    /**
     * Scope a query by search term.
     *
     * @param Builder $query
     * @param string $searchTerm
     * @return void
     */
    #[Scope]
    protected function search(Builder $query, string $searchTerm): void
    {
        $query->where('number', 'like', '%' . $searchTerm . '%')
              ->orWhere('customer_first_name', 'like', '%' . $searchTerm . '%')
              ->orWhere('customer_last_name', 'like', '%' . $searchTerm . '%')
              ->orWhere('customer_email', 'like', '%' . $searchTerm . '%')
              ->orWhere('customer_mobile_number', 'like', '%' . $searchTerm . '%')
              ->orWhere('tracking_number', 'like', '%' . $searchTerm . '%')
              ->orWhere('collection_code', 'like', '%' . $searchTerm . '%');
    }

    /**
     * Get store.
     *
     * @return BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get customer
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get user who placed order.
     *
     * @return BelongsTo
     */
    public function placedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'placed_by_user_id');
    }

    /**
     * Get user who created order.
     *
     * @return BelongsTo
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Get user assigned to order.
     *
     * @return BelongsTo
     */
    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    /**
     * Get courier.
     *
     * @return BelongsTo
     */
    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    /**
     * Get delivery method.
     *
     * @return BelongsTo
     */
    public function deliveryMethod(): BelongsTo
    {
        return $this->belongsTo(DeliveryMethod::class);
    }

    /**
     * Get delivery address.
     *
     * @return HasOne
     */
    public function deliveryAddress(): HasOne
    {
        return $this->hasOne(DeliveryAddress::class);
    }

    /**
     * Get user who verified collection.
     *
     * @return BelongsTo
     */
    public function collectionVerifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collection_verified_by_user_id');
    }

    /**
     * Get order products.
     *
     * @return HasMany
     */
    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    /**
     * Get order products.
     *
     * @return HasMany
     */
    public function orderPromotions(): HasMany
    {
        return $this->hasMany(OrderPromotion::class);
    }

    /**
     * Get order discounts.
     *
     * @return HasMany
     */
    public function orderDiscounts(): HasMany
    {
        return $this->hasMany(OrderDiscount::class);
    }

    /**
     * Get order fees.
     *
     * @return HasMany
     */
    public function orderFees(): HasMany
    {
        return $this->hasMany(OrderFee::class);
    }

    /**
     * Get order comments.
     *
     * @return HasMany
     */
    public function orderComments(): HasMany
    {
        return $this->hasMany(OrderComment::class);
    }

    /**
     * Get transactions.
     *
     * @return MorphMany
     */
    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'owner');
    }

    /**
     * Get customer's name.
     *
     * @return Attribute
     */
    protected function customerName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->customer_first_name} {$this->customer_last_name}")
        );
    }
}
