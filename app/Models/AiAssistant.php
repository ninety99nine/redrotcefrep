<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiAssistant extends Model
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
            'total_paid_tokens' => 'integer',
            'remaining_free_tokens' => 'integer',
            'remaining_paid_tokens' => 'integer',
            'remaining_paid_top_up_tokens' => 'integer',

            'requires_subscription' => 'boolean',
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'total_paid_tokens','remaining_free_tokens','remaining_paid_tokens','remaining_paid_top_up_tokens',
        'requires_subscription','user_id',
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

    /**
     * Get the user.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transactions.
     *
     * @return HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the ai assistant token usage.
     *
     * @return HasMany
     */
    public function aiAssistantTokenUsage(): HasOne
    {
        return $this->hasOne(AiAssistantTokenUsage::class);
    }

    /**
     *  Get the subscriptions.
     */
    public function subscriptions()
    {
        return $this->morphMany(Subscription::class, 'owner');
    }

    /**
     *  Get active subscription.
     *
     * @return MorphOne
     */
    public function activeSubscription(): MorphOne
    {
        return $this->morphOne(Subscription::class, 'owner')->active();
    }
}
