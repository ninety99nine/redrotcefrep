<?php

namespace App\Models;

use App\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast;
use Illuminate\Database\Eloquent\Relations\belongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, HasUuids;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
            'mobile_number' => E164PhoneNumberCast::class
        ];
    }

    protected $fillable = [
        'first_name', 'last_name', 'email', 'mobile_number', 'google_id', 'facebook_id', 'linkedin_id', 'password', 'email_verified_at'
    ];

    protected $hidden = [
        'password', 'remember_token'
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
        return $this->belongsToMany(Store::class, 'store_follower')
                    ->using(StoreFollower::class)
                    ->as('store_follower')
                    ->withTimestamps();
    }

    /**
     * Get followed stores.
     *
     * @return BelongsToMany
     */
    public function followedStores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_follower')->withTimestamps();
    }

    /**
     * Get visited stores.
     *
     * @return BelongsToMany
     */
    public function visitedStores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_visitor')
                    ->using(StoreVisitor::class)
                    ->as('store_visitor')
                    ->withPivot([
                        'guest_id', 'last_visited_at'
                    ]);
    }

    /**
     * Get AI assistant.
     *
     * @return HasOne
     */
    public function aiAssistant(): HasOne
    {
        return $this->hasOne(AiAssistant::class);
    }

    /**
     * Get AI messages.
     *
     * @return HasOne
     */
    public function aiMessages(): HasMany
    {
        return $this->hasMany(AiMessage::class);
    }

    /**
     * Get user's full name.
     *
     * @return Attribute
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->first_name} {$this->last_name}")
        );
    }
}
