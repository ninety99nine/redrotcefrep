<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\belongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;

class StoreVisitor extends Model
{
    use HasFactory, HasUuids, AsPivot;

    protected $table = 'store_visitor';

    /**
     * Disable automatic timestamps since this table doesn't have created_at/updated_at columns
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected function casts(): array
    {
        return [
            'last_visited_at' => 'date'
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'store_id', 'guest_id', 'last_visited_at'
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
