<?php

namespace App\Models;

use App\Casts\Money;
use App\Casts\JsonArray;
use App\Traits\PricingPlanTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PricingPlan extends Model
{
    use HasFactory, HasUuids, PricingPlanTrait;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'supports_web' => 'boolean',
            'supports_ussd' => 'boolean',
            'supports_mobile' => 'boolean',

            'position' => 'integer',
            'trial_days' => 'integer',
            'discount_percentage_rate' => 'integer',
            'max_auto_billing_attempts' => 'integer',

            'price' => Money::class,

            'metadata' => JsonArray::class,
            'features' => JsonArray::class,
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'active','name','type','description','billing_type','trial_days','currency','price','discount_percentage_rate',
        'max_auto_billing_attempts','auto_billing_disabled_sms_message','supports_web','supports_ussd',
        'supports_mobile','metadata','features','position',
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
                  ->orWhere('type', 'like', '%' . $searchTerm . '%');
        });
    }

    public function scopeActive($query)
    {
        return $query->where('active', '1');
    }

    public function scopeInactive($query)
    {
        return $query->where('active', '0');
    }

    public function scopeSupportsWeb($query)
    {
        return $query->where('supports_web', '1');
    }

    public function scopeSupportsUssd($query)
    {
        return $query->where('supports_ussd', '1');
    }

    public function scopeSupportsMobile($query)
    {
        return $query->where('supports_mobile', '1');
    }
}
