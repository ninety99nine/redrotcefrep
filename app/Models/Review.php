<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast;

class Review extends Model
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
            'rating' => 'integer',
            'visible' => 'boolean',
            'mobile_number' => E164PhoneNumberCast::class,
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'visible','name','mobile_number','rating','comment','user_id','store_id',
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
            $query->where('rating', $searchTerm)
                  ->orWhere('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('comment', 'like', '%' . $searchTerm . '%');
        });
    }

    public function scopeVisible($query)
    {
        return $query->where('visible', '1');
    }

    public function scopeHidden($query)
    {
        return $query->where('visible', '0');
    }

    /**
     * Get user that wrote the review.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get store that was reviewed.
     *
     * @return BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
