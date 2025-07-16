<?php

namespace App\Models;

use App\Casts\Money;
use App\Casts\JsonArray;
use App\Traits\ShoppingTrait;
use App\Enums\UploadFolderName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderProduct extends Model
{
    use HasFactory, HasUuids, ShoppingTrait;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected function casts(): array
    {
        return [
            'unit_weight' => 'decimal:2',

            'quantity' => 'integer',
            'original_quantity' => 'integer',
            'unit_loss_percentage' => 'integer',
            'unit_profit_percentage' => 'integer',
            'unit_sale_discount_percentage' => 'integer',

            'is_free' => 'boolean',
            'on_sale' => 'boolean',
            'has_price' => 'boolean',
            'is_cancelled' => 'boolean',
            'has_limited_stock' => 'boolean',
            'has_exceeded_maximum_allowed_quantity_per_order' => 'boolean',

            'subtotal' => Money::class,
            'unit_loss' => Money::class,
            'unit_price' => Money::class,
            'unit_profit' => Money::class,
            'grand_total' => Money::class,
            'unit_cost_price' => Money::class,
            'unit_sale_price' => Money::class,
            'unit_sale_discount' => Money::class,
            'unit_regular_price' => Money::class,
            'sale_discount_total' => Money::class,

            'detected_changes' => JsonArray::class,
            'cancellation_reasons' => JsonArray::class,
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','description','sku','barcode','unit_weight','is_free','currency','unit_regular_price','on_sale',
        'unit_sale_price','unit_sale_discount','unit_sale_discount_percentage','unit_cost_price','has_price',
        'unit_price','unit_profit','unit_profit_percentage','unit_loss','unit_loss_percentage','sale_discount_total',
        'grand_total','subtotal','original_quantity','quantity','has_limited_stock','has_exceeded_maximum_allowed_quantity_per_order',
        'is_cancelled','cancellation_reasons','detected_changes','order_id','store_id','product_id'
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

    /**
     * Get the order.
     *
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
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
     * Get the product.
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the photo.
     *
     * @return BelongsTo
     */
    public function photo()
    {
        return $this->hasOneThrough(
            MediaFile::class,  // The target model (photo)
            Product::class,    // The intermediate model (product)
            'id',              // Foreign key on the Product table (order_products.product_id = products.id)
            'mediable_id',     // Foreign key on the MediaFile table (photos.mediable_id = products.id)
            'product_id',      // Foreign key on the OrderProduct table (order_products.product_id)
            'id'               // Primary key on the Product table
        )->where('type', UploadFolderName::PRODUCT_PHOTO->value);
    }
}
