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
            $table->string('custom_name', 40)->nullable();
            $table->string('instruction')->nullable();
            $table->json('configs')->nullable();
            $table->boolean('requires_verification')->default(0);
            $table->boolean('require_proof_of_payment')->default(0);
            $table->boolean('enable_contact_seller_before_payment')->default(1);
            $table->boolean('mark_as_paid_on_customer_confirmation')->default(0);
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
