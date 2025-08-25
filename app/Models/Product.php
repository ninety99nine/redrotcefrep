<?php

namespace App\Models;

use App\Casts\Money;
use App\Casts\JsonArray;
use App\Traits\ProductTrait;
use App\Enums\UploadFolderName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Relations\hasOne;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\hasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory, HasUuids, ProductTrait;

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
            'tax_overide' => 'boolean',
            'show_description' => 'boolean',
            'is_estimated_price' => 'boolean',
            'set_daily_capacity' => 'boolean',
            'show_price_per_unit' => 'boolean',
            'set_min_order_quantity' => 'boolean',
            'set_max_order_quantity' => 'boolean',
            'visibility_expires_at' => 'datetime',

            'unit_loss' => Money::class,
            'unit_price' => Money::class,
            'unit_profit' => Money::class,
            'unit_sale_price' => Money::class,
            'unit_cost_price' => Money::class,
            'unit_sale_discount' => Money::class,
            'unit_regular_price' => Money::class,
            'tax_overide_amount' => Money::class,

            'data_collection_fields' => JsonArray::class,

            'unit_value' => 'decimal:3',
            'unit_weight' => 'decimal:3',
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'type', 'visible', 'visibility_expires_at', 'show_description', 'description', 'sku', 'barcode',
        'unit_weight', 'is_free', 'is_estimated_price', 'show_price_per_unit', 'tax_overide',
        'tax_overide_amount', 'download_link', 'unit_type', 'unit_value', 'currency', 'unit_regular_price', 'on_sale',
        'unit_sale_price', 'unit_sale_discount', 'unit_sale_discount_percentage', 'unit_cost_price', 'has_price',
        'unit_price', 'unit_profit', 'unit_profit_percentage', 'unit_loss', 'unit_loss_percentage', 'set_min_order_quantity',
        'set_max_order_quantity', 'min_order_quantity', 'max_order_quantity', 'set_daily_capacity', 'daily_capacity',
        'has_stock', 'stock_quantity_type', 'stock_quantity', 'position', 'parent_product_id', 'user_id', 'store_id',
        'data_collection_fields'
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

    public function scopeVisible($query)
    {
        return $query->where('visible', '1');
    }

    public function scopeIsNotVariant($query)
    {
        return $query->whereNull('parent_product_id');
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
     * Get store.
     *
     * @return BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get product variant.
     *
     * @return hasOne
     */
    public function variant(): hasOne
    {
        return $this->hasOne(Product::class, 'parent_product_id')->orderBy('position');
    }

    /**
     * Get product variants.
     *
     * @return hasMany
     */
    public function variants(): hasMany
    {
        return $this->hasMany(Product::class, 'parent_product_id')->orderBy('position');
    }

    /**
     * Get parent product.
     *
     * @return BelongsTo
     */
    public function parentProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'parent_product_id');
    }

    /**
     * Get tags.
     *
     * @return BelongsToMany
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }

    /**
     * Get categories.
     *
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_category');
    }

    /**
     * Get delivery methods.
     *
     * @return BelongsToMany
     */
    public function deliveryMethods(): BelongsToMany
    {
        return $this->belongsToMany(DeliveryMethod::class, 'product_delivery_method');
    }

    /**
     * Get photo.
     *
     * @return MorphOne
     */
    public function photo(): MorphOne
    {
        return $this->morphOne(MediaFile::class, 'mediable')->where('type', UploadFolderName::PRODUCT_PHOTO->value);
    }

    /**
     * Get photos.
     *
     * @return MorphMany
     */
    public function photos(): MorphMany
    {
        return $this->morphMany(MediaFile::class, 'mediable')->where('type', UploadFolderName::PRODUCT_PHOTO->value);
    }

    /**
     * Get media files (photos and other media file types).
     *
     * @return MorphMany
     */
    public function mediaFiles(): MorphMany
    {
        return $this->morphMany(MediaFile::class, 'mediable');
    }
}
