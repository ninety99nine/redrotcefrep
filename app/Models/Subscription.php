<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Subscription extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'end_at' => 'datetime',
        'start_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'start_at','end_at','user_id','transaction_id','pricing_plan_id','owner_id','owner_type',
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
        $query->where('user_id', 'like', '%' . $searchTerm . '%');
    }

    public function scopeActive($query)
    {
        return $query->where('start_at', '<=', Carbon::now())->where('end_at', '>=', Carbon::now());
    }

    public function scopeExpired($query)
    {
        return $query->where('end_at', '<', Carbon::now());
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
     * Get transaction.
     *
     * @return BelongsTo
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get pricing plan.
     *
     * @return BelongsTo
     */
    public function pricingPlan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class);
    }

    /**
     * Get parent owner model (User or Store).
     *
     * @return MorphTo
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    protected $appends = [
        'status'
    ];

    public function status(): Attribute
    {
        return new Attribute(
            get: function() {
                if (Carbon::parse($this->end_at)->isPast()) {
                    return 'Expired';
                }else if (Carbon::parse($this->start_at)->isFuture()) {
                    return 'Scheduled';
                }else{
                    return 'Active';
                }
            }
        );
    }
}
