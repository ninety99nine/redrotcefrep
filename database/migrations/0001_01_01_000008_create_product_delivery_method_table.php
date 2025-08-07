<?php

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
        Schema::create('product_delivery_method', function (Blueprint $table) {
            $table->primary(['product_id', 'delivery_method_id']);
            $table->foreignUuid('product_id');
            $table->foreignUuid('delivery_method_id');
            $table->timestamps();

            $table->index('product_id');
            $table->index('delivery_method_id');

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('delivery_method_id')->references('id')->on('delivery_methods')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_delivery_method');
    }
};
