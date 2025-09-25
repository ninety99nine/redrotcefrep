<?php

namespace App\Models;

use App\Casts\JsonArray;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\belongsToMany;

class PaymentMethod extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
        'automated_verification' => 'boolean',

        'countries' => JsonArray::class,
        'currencies' => JsonArray::class,
        'ussd_codes' => JsonArray::class,
        'config_schema' => JsonArray::class,
        'allowed_countries' => JsonArray::class,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'active','name','type','automated_verification','currencies','countries','allowed_countries','ussd_codes','config_schema','position',
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

    /**
     * Get stores.
     *
     * @return BelongsToMany
     */
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_payment_method', 'payment_method_id', 'store_id')
                    ->withPivot([
                        'id', 'active', 'custom_name', 'instruction', 'configs', 'require_proof_of_payment',
                        'enable_contact_seller_before_payment', 'mark_as_paid_on_customer_confirmation',
                        'position', 'store_id', 'payment_method_id', 'created_at', 'updated_at'
                    ])
                    ->using(StorePaymentMethod::class)
                    ->as('store_payment_method');
    }

    /**
     * Get image url.
     *
     * @return Attribute
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => asset('/images/payment-method-logos/'.$this->type.'.jpg')
        );
    }
}
