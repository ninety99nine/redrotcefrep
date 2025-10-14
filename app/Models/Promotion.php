<?php

namespace App\Models;

use App\Casts\Money;
use App\Casts\JsonArray;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

            'discount_percentage_rate' => 'float',

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
                  ->orWhere('code', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
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

    /**
     * Get promotion instructions formatted for user-friendly display.
     *
     * @return Attribute
     */
    protected function instructions(): Attribute
    {
        return Attribute::make(
            get: function () {
                $instructions = [];

                // Promotion code requirement
                if ($this->activate_using_code) {
                    $instructions[] = "Use the code <strong>{$this->code}</strong> at checkout";
                }

                // Date and time restrictions
                if ($this->activate_using_start_datetime && $this->activate_using_end_datetime) {
                    $start = Carbon::parse($this->start_datetime)->format('M d, Y h:i A');
                    $end = Carbon::parse($this->end_datetime)->format('M d, Y h:i A');
                    $instructions[] = "Order between <strong>{$start}</strong> and <strong>{$end}</strong>";
                } elseif ($this->activate_using_start_datetime) {
                    $start = Carbon::parse($this->start_datetime)->format('M d, Y h:i A');
                    $instructions[] = "Order after <strong>{$start}</strong>";
                } elseif ($this->activate_using_end_datetime) {
                    $end = Carbon::parse($this->end_datetime)->format('M d, Y h:i A');
                    $instructions[] = "Order before <strong>{$end}</strong>";
                }

                // Hours of the day
                if ($this->activate_using_hours_of_day && !empty($this->hours_of_day)) {
                    $hours = collect($this->hours_of_day)->join(', ', ' or ');
                    $instructions[] = "Order during these hours: <strong>{$hours}</strong>";
                }

                // Days of the week
                if ($this->activate_using_days_of_the_week && !empty($this->days_of_the_week)) {
                    $days = collect($this->days_of_the_week)->map(fn($day) => ucfirst($day))->join(', ', ' or ');
                    $instructions[] = "Order on <strong>{$days}</strong>";
                }

                // Days of the month
                if ($this->activate_using_days_of_the_month && !empty($this->days_of_the_month)) {
                    $days = collect($this->days_of_the_month)->join(', ', ' or ');
                    $instructions[] = "Order on these days of the month: <strong>{$days}</strong>";
                }

                // Months of the year
                if ($this->activate_using_months_of_the_year && !empty($this->months_of_the_year)) {
                    $months = collect($this->months_of_the_year)->map(fn($month) => ucfirst($month))->join(', ', ' or ');
                    $instructions[] = "Order in <strong>{$months}</strong>";
                }

                // Minimum total products
                if ($this->activate_using_minimum_total_products) {
                    $products = $this->minimum_total_products == 1 ? '1 product' : "{$this->minimum_total_products} different products";
                    $instructions[] = "Order <strong>{$products}</strong> or more";
                }

                // Minimum total product quantities
                if ($this->activate_using_minimum_total_product_quantities) {
                    $quantities = $this->minimum_total_product_quantities == 1 ? '1 item' : "{$this->minimum_total_product_quantities} items";
                    $instructions[] = "Order a total of <strong>{$quantities}</strong> or more";
                }

                // Minimum grand total
                if ($this->activate_using_minimum_grand_total) {
                    $amount = $this->minimum_grand_total->amount_with_currency;
                    $instructions[] = "Spend <strong>{$amount}</strong> or more";
                }

                // Customer type restrictions
                if ($this->activate_for_new_customer && $this->activate_for_existing_customer) {
                    $instructions[] = "Available for both new and existing customers";
                } elseif ($this->activate_for_new_customer) {
                    $instructions[] = "For new customers only";
                } elseif ($this->activate_for_existing_customer) {
                    $instructions[] = "For existing customers only";
                }

                // Usage limit
                if ($this->activate_using_usage_limit) {
                    $remaining = $this->remaining_quantity == 1 ? '1 use' : "{$this->remaining_quantity} uses";
                    $instructions[] = "Limited offer! Only <strong>{$remaining}</strong> left";
                }

                // Default instruction if no conditions apply
                if (empty($instructions)) {
                    $instructions[] = "Just place an order to enjoy this offer!";
                }

                return $instructions;
            }
        );
    }
}
