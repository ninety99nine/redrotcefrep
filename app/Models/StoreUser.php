<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\belongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;

class StoreUser extends Model
{
    use HasFactory, HasUuids, AsPivot;

    protected $table = 'store_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'store_id', 'created_at', 'updated_at'
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
