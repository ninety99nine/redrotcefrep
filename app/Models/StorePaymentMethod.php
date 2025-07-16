<?php

namespace App\Models;

use App\Casts\JsonArray;
use App\Enums\UploadFolderName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\morphOne;
use Illuminate\Database\Eloquent\Relations\morphMany;
use Illuminate\Database\Eloquent\Relations\belongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;

class StorePaymentMethod extends Model
{
    use HasFactory, HasUuids, AsPivot;

    protected $table = 'store_payment_method';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
        'configs' => JsonArray::class
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'active', 'custom_name', 'instruction', 'configs', 'position',
        'store_id', 'payment_method_id', 'created_at', 'updated_at'
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
        $query->where('custom_name', 'like', '%' . $searchTerm . '%');
    }

    /**
     * Get the store.
     *
     * @return BelongsToMany
     */
    public function store(): belongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the payment method.
     *
     * @return BelongsToManyToMany
     */
    public function paymentMethod(): belongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the logo.
     *
     * @return MorphOne
     */
    public function logo(): MorphOne
    {
        return $this->morphOne(MediaFile::class, 'mediable')->where('type', UploadFolderName::STORE_PAYMENT_METHOD_LOGO->value);
    }

    /**
     * Get the photo.
     *
     * @return MorphOne
     */
    public function photo(): MorphOne
    {
        return $this->morphOne(MediaFile::class, 'mediable')->where('type', UploadFolderName::STORE_PAYMENT_METHOD_PHOTO->value);
    }

    /**
     * Get the media files.
     *
     * @return MorphMany
     */
    public function mediaFiles(): MorphMany
    {
        return $this->morphMany(MediaFile::class, 'mediable');
    }
}
