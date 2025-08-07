<?php

namespace App\Models;

use App\Casts\Money;
use App\Casts\JsonArray;
use App\Traits\DeliveryMethodTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DeliveryMethod extends Model
{
    use HasFactory, HasUuids, DeliveryMethodTrait;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [

        'distance_zones' => JsonArray::class,
        'weight_categories' => JsonArray::class,
        'postal_code_zones' => JsonArray::class,
        'additional_fields' => JsonArray::class,
        'operational_hours' => JsonArray::class,

        'flat_fee_rate' => Money::class,
        'minimum_grand_total' => Money::class,
        'fallback_flat_fee_rate' => Money::class,
        'free_delivery_minimum_grand_total' => Money::class,

        'percentage_fee_rate' => 'decimal:2',
        'fallback_percentage_fee_rate' => 'decimal:2',

        'position' => 'integer',
        'daily_order_limit' => 'integer',
        'time_slot_interval_value' => 'integer',
        'latest_delivery_time_value' => 'integer',
        'earliest_delivery_time_value' => 'integer',

        'active' => 'boolean',
        'charge_fee' => 'boolean',
        'set_schedule' => 'boolean',
        'same_day_delivery' => 'boolean',
        'ask_for_an_address' => 'boolean',
        'pin_location_on_map' => 'boolean',
        'set_daily_order_limit' => 'boolean',
        'show_distance_on_invoice' => 'boolean',
        'auto_generate_time_slots' => 'boolean',
        'capture_additional_fields' => 'boolean',
        'qualify_on_minimum_grand_total' => 'boolean',
        'require_minimum_notice_for_orders' => 'boolean',
        'restrict_maximum_notice_for_orders' => 'boolean',
        'offer_free_delivery_on_minimum_grand_total' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'active','name','description','currency','qualify_on_minimum_grand_total','minimum_grand_total',
        'offer_free_delivery_on_minimum_grand_total','free_delivery_minimum_grand_total','ask_for_an_address',
        'pin_location_on_map','show_distance_on_invoice','charge_fee','fee_type','percentage_fee_rate',
        'flat_fee_rate','weight_categories','distance_zones','postal_code_zones','fallback_fee_type',
        'fallback_percentage_fee_rate','fallback_flat_fee_rate','set_schedule','schedule_type','operational_hours',
        'auto_generate_time_slots','time_slot_interval_value','time_slot_interval_unit','same_day_delivery',
        'require_minimum_notice_for_orders','earliest_delivery_time_value','earliest_delivery_time_unit',
        'restrict_maximum_notice_for_orders','latest_delivery_time_value','set_daily_order_limit',
        'daily_order_limit','capture_additional_fields','additional_fields','position','store_id',
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
        $query->where('name', 'like', '%' . $searchTerm . '%');
    }

    public function scopeActive($query)
    {
        return $query->where('active', '1');
    }

    public function scopeInactive($query)
    {
        return $query->where('active', '0');
    }

    /**
     * Get the store.
     *
     * @return BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the address.
     *
     * @return BelongsTo
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * Get the products.
     *
     * @return BelongsToMany
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_delivery_method');
    }
}
