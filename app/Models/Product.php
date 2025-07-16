<?php

namespace App\Models;

use App\Casts\Money;
use App\Casts\JsonArray;
use App\Enums\UploadFolderName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\hasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
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
            'visible' => 'boolean',
            'is_free' => 'boolean',
            'on_sale' => 'boolean',
            'has_price' => 'boolean',
            'has_stock' => 'boolean',
            'show_description' => 'boolean',
            'allow_variations' => 'boolean',

            'visibility_expires_at' => 'datetime',

            'unit_loss' => Money::class,
            'unit_price' => Money::class,
            'unit_profit' => Money::class,
            'unit_sale_price' => Money::class,
            'unit_cost_price' => Money::class,
            'unit_sale_discount' => Money::class,
            'unit_regular_price' => Money::class,

            'unit_weight' => 'decimal:3',
            'variant_attributes' => JsonArray::class,
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','visible','visibility_expires_at','show_description','description','sku','barcode','allow_variations',
        'variant_attributes','total_variations','total_visible_variations','unit_weight','is_free','currency',
        'unit_regular_price','on_sale','unit_sale_price','unit_sale_discount','unit_sale_discount_percentage',
        'unit_cost_price','has_price','unit_price','unit_profit','unit_profit_percentage','unit_loss',
        'unit_loss_percentage','allowed_quantity_per_order','maximum_allowed_quantity_per_order',
        'has_stock','stock_quantity_type','stock_quantity','position','parent_product_id',
        'user_id','store_id',
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
        $query->where('name', 'like', '%' . $searchTerm . '%')
              ->orWhere('sku', 'like', '%' . $searchTerm . '%')
              ->orWhere('barcode', 'like', '%' . $searchTerm . '%')
              ->orWhere('description', 'like', '%' . $searchTerm . '%');
    }

    public function scopeSupportsVariations($query)
    {
        return $query->where('allow_variations', '1');
    }

    public function scopeDoesNotSupportVariations($query)
    {
        return $query->where('allow_variations', '0');
    }

    public function scopeVisible($query)
    {
        return $query->where('visible', '1');
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
     * Get the store.
     *
     * @return BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the product variations.
     *
     * @return hasMany
     */
    public function variations(): hasMany
    {
        return $this->hasMany(Product::class, 'parent_product_id');
    }

    /**
     * Get the parent product.
     *
     * @return BelongsTo
     */
    public function parentProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'parent_product_id');
    }

    /**
     * Get the photo.
     *
     * @return MorphOne
     */
    public function photo(): MorphOne
    {
        return $this->morphOne(MediaFile::class, 'mediable')->where('type', UploadFolderName::PRODUCT_PHOTO->value);
    }

    /**
     * Get the photos.
     *
     * @return MorphMany
     */
    public function photos(): MorphMany
    {
        return $this->morphMany(MediaFile::class, 'mediable')->where('type', UploadFolderName::PRODUCT_PHOTO->value);
    }

    /**
     * Get the media files (photos and other media file types).
     *
     * @return MorphMany
     */
    public function mediaFiles(): MorphMany
    {
        return $this->morphMany(MediaFile::class, 'mediable');
    }
}
