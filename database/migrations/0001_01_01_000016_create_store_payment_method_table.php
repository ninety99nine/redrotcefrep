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
        Schema::create('store_payment_method', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->boolean('active')->default(0);
            $table->string('custom_name', 20)->nullable();
            $table->string('instruction')->nullable();
            $table->json('configs')->nullable();
            $table->unsignedTinyInteger('position')->nullable();
            $table->foreignUuid('store_id');
            $table->foreignUuid('payment_method_id');
            $table->timestamps();

            $table->index('store_id');
            $table->index('position');
            $table->index('payment_method_id');

            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_payment_method');
    }
};
