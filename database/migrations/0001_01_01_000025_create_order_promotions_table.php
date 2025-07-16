<?php

use App\Enums\RateType;
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
        Schema::create('order_promotions', function (Blueprint $table) {
             $table->uuid('id')->primary();
            $table->string('name', 50);
            $table->string('description', 500)->nullable();
            $table->boolean('offer_discount')->default(false);
            $table->enum('discount_rate_type', RateType::values())->default(RateType::FLAT->value);
            $table->unsignedTinyInteger('discount_percentage_rate')->default(0);
            $table->decimal('discount_flat_rate', 12, 3)->default(0);
            $table->boolean('offer_free_delivery')->default(false);
            $table->boolean('activate_using_code')->default(false);
            $table->string('code', 10)->nullable();
            $table->boolean('activate_using_minimum_grand_total')->default(false);
            $table->decimal('minimum_grand_total', 12, 3)->default(0);
            $table->char('currency', 3)->default(config('app.currency'));
            $table->boolean('activate_using_minimum_total_products')->default(false);
            $table->unsignedSmallInteger('minimum_total_products')->default(1);
            $table->boolean('activate_using_minimum_total_product_quantities')->default(false);
            $table->unsignedSmallInteger('minimum_total_product_quantities')->default(1);
            $table->boolean('activate_using_start_datetime')->default(false);
            $table->timestamp('start_datetime')->nullable();
            $table->boolean('activate_using_end_datetime')->default(false);
            $table->timestamp('end_datetime')->nullable();
            $table->boolean('activate_using_hours_of_day')->default(false);
            $table->json('hours_of_day')->nullable();
            $table->boolean('activate_using_days_of_the_week')->default(false);
            $table->json('days_of_the_week')->nullable();
            $table->boolean('activate_using_days_of_the_month')->default(false);
            $table->json('days_of_the_month')->nullable();
            $table->boolean('activate_using_months_of_the_year')->default(false);
            $table->json('months_of_the_year')->nullable();
            $table->boolean('activate_for_new_customer')->default(false);
            $table->boolean('activate_for_existing_customer')->default(false);
            $table->boolean('activate_using_usage_limit')->default(false);
            $table->unsignedMediumInteger('remaining_quantity')->default(0);
            $table->boolean('is_cancelled')->default(false);
            $table->json('cancellation_reasons')->nullable();
            $table->json('detected_changes')->nullable();
            $table->foreignUuid('order_id');
            $table->foreignUuid('store_id');
            $table->foreignUuid('promotion_id')->nullable();
            $table->timestamps();

            $table->index('name');
            $table->index('code');
            $table->index('store_id');
            $table->index('promotion_id');

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('promotion_id')->references('id')->on('promotions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_promotions');
    }
};
