<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\belongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;

class StoreFollower extends Model
{
    use HasFactory, HasUuids, AsPivot;

    protected $table = 'store_follower';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected function casts(): array
    {
        return [
            'last_visited_at' => 'datetime'
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'store_id', 'user_id', 'guest_id', 'last_visited_at'
    ];

    /**
     * Get user.
     *
     * @return belongsTo
     */
    public function user(): belongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get store.
     *
     * @return belongsTo
     */
    public function store(): belongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
