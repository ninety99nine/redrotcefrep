<?php

namespace App\Models;

use App\Services\CountryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryAddress extends Model
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
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8'
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type','address_line','address_line2','city','state','postal_code','country',
        'place_id','latitude','longitude','description','order_id'
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
        $query->where('address_line', 'like', '%' . $searchTerm . '%')
              ->orWhere('address_line2', 'like', '%' . $searchTerm . '%')
              ->orWhere('city', 'like', '%' . $searchTerm . '%')
              ->orWhere('state', 'like', '%' . $searchTerm . '%')
              ->orWhere('postal_code', 'like', '%' . $searchTerm . '%')
              ->orWhere('country', 'like', '%' . $searchTerm . '%')
              ->orWhere('place_id', 'like', '%' . $searchTerm . '%');
    }

    /**
     * Get order.
     *
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get complete address.
     *
     * @return Attribute
     */
    protected function completeAddress(): Attribute
    {
        return Attribute::make(
            get: function() {
                $countryName = function() {
                    if(empty($this->country)) return '';
                    return CountryService::findCountryNameByTwoLetterCountryCode($this->country) ?? '';
                };
                return collect([$this->address_line, $this->address_line2, $this->city, $this->state, $this->postal_code, $countryName()])->map('trim')->filter()->unique()->join(', ');
            }
        );
    }
}
