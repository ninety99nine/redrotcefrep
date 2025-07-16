<?php

use App\Enums\StockQuantityType;
use App\Enums\AllowedQuantityPerOrder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 60)->nullable()->comment('Product name, up to 60 characters to accommodate variation names (e.g., "Nike Winter Jacket (Red, Large, Cotton)")');
            $table->boolean('visible')->default(true);
            $table->timestamp('visibility_expires_at')->nullable();
            $table->boolean('show_description')->default(false);
            $table->string('description', 500)->nullable();
            $table->string('sku', 50)->nullable();
            $table->string('barcode', 50)->nullable();
            $table->boolean('allow_variations')->default(false);
            $table->json('variant_attributes')->nullable();
            $table->unsignedTinyInteger('total_variations')->nullable();
            $table->unsignedTinyInteger('total_visible_variations')->nullable();
            $table->decimal('unit_weight', 12, 3)->default(0);
            $table->boolean('is_free')->default(false);
            $table->char('currency', 3)->default(config('app.currency'));
            $table->decimal('unit_regular_price', 12, 3)->default(0);
            $table->boolean('on_sale')->default(false);
            $table->decimal('unit_sale_price', 12, 3)->default(0);
            $table->decimal('unit_sale_discount', 12, 3)->default(0);
            $table->unsignedSmallInteger('unit_sale_discount_percentage')->default(0);
            $table->decimal('unit_cost_price', 12, 3)->default(0);
            $table->boolean('has_price')->default(false);
            $table->decimal('unit_price', 12, 3)->default(0);
            $table->decimal('unit_profit', 12, 3)->default(0);
            $table->unsignedSmallInteger('unit_profit_percentage')->default(0);
            $table->decimal('unit_loss', 12, 3)->default(0);
            $table->unsignedSmallInteger('unit_loss_percentage')->default(0);
            $table->enum('allowed_quantity_per_order', AllowedQuantityPerOrder::values())->default(AllowedQuantityPerOrder::UNLIMITED->value);
            $table->unsignedSmallInteger('maximum_allowed_quantity_per_order')->default(1);
            $table->boolean('has_stock')->default(true);
            $table->enum('stock_quantity_type', StockQuantityType::values())->default(StockQuantityType::UNLIMITED->value);
            $table->unsignedMediumInteger('stock_quantity')->default(100);
            $table->unsignedTinyInteger('position')->nullable();
            $table->uuid('parent_product_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->uuid('store_id');
            $table->timestamps();

            $table->index('name');
            $table->index('sku');
            $table->index('barcode');
            $table->index('user_id');
            $table->index('store_id');
            $table->index('position');
            $table->index('parent_product_id');

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('parent_product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
