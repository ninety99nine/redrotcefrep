<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_products', function (Blueprint $table) {
             $table->uuid('id')->primary();
            $table->string('name', 60)->nullable();
            $table->string('description', 500)->nullable();
            $table->string('sku', 100)->nullable();
            $table->string('barcode', 100)->nullable();
            $table->decimal('unit_weight', 12, 2)->default(0);
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
            $table->decimal('sale_discount_total', 12, 3)->default(0);
            $table->decimal('grand_total', 12, 3)->default(0);
            $table->decimal('subtotal', 12, 3)->default(0);
            $table->unsignedSmallInteger('original_quantity')->default(1);
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->boolean('has_limited_stock')->default(false);
            $table->boolean('has_exceeded_maximum_allowed_quantity_per_order')->default(false);
            $table->boolean('is_cancelled')->default(false);
            $table->json('cancellation_reasons')->nullable();
            $table->json('detected_changes')->nullable();
            $table->foreignUuid('order_id');
            $table->foreignUuid('store_id');
            $table->foreignUuid('product_id')->nullable();
            $table->timestamps();

            $table->index('sku');
            $table->index('name');
            $table->index('barcode');
            $table->index('store_id');
            $table->index('product_id');

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_products');
    }
};
