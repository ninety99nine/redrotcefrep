<?php

namespace App\Models;

use App\Casts\Money;
use App\Casts\JsonArray;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Promotion extends Model
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
            'end_datetime' => 'datetime',
            'start_datetime' => 'datetime',

            'active' => 'boolean',
            'offer_discount' => 'boolean',
            'offer_free_delivery' => 'boolean',
            'activate_using_code' => 'boolean',
            'activate_for_new_customer' => 'boolean',
            'activate_using_usage_limit' => 'boolean',
            'activate_using_end_datetime' => 'boolean',
            'activate_using_hours_of_day' => 'boolean',
            'activate_using_start_datetime' => 'boolean',
            'activate_for_existing_customer' => 'boolean',
            'activate_using_days_of_the_week' => 'boolean',
            'activate_using_days_of_the_month' => 'boolean',
            'activate_using_months_of_the_year' => 'boolean',
            'activate_using_minimum_grand_total' => 'boolean',
            'activate_using_minimum_total_products' => 'boolean',
            'activate_using_minimum_total_product_quantities' => 'boolean',

            'discount_percentage_rate' => 'decimal:2',

            'discount_flat_rate' => Money::class,
            'minimum_grand_total' => Money::class,

            'hours_of_day' => JsonArray::class,
            'days_of_the_week' => JsonArray::class,
            'days_of_the_month' => JsonArray::class,
            'months_of_the_year' => JsonArray::class,
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','description','active','offer_discount','discount_rate_type','discount_percentage_rate','discount_flat_rate',
        'offer_free_delivery','activate_using_code','code','activate_using_minimum_grand_total','minimum_grand_total',
        'currency','activate_using_minimum_total_products','minimum_total_products','activate_using_minimum_total_product_quantities',
        'minimum_total_product_quantities','activate_using_start_datetime','start_datetime','activate_using_end_datetime',
        'end_datetime','activate_using_hours_of_day','hours_of_day','activate_using_days_of_the_week','days_of_the_week',
        'activate_using_days_of_the_month','days_of_the_month','activate_using_months_of_the_year','months_of_the_year',
        'activate_for_new_customer','activate_for_existing_customer','activate_using_usage_limit','remaining_quantity',
        'store_id','user_id',
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
        $query->where(function ($query) use ($searchTerm) {
            $query->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('code', 'like', '%' . $searchTerm . '%');
        });
    }

    /**
     * Get user.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
}
