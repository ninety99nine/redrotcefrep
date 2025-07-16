<?php

use App\Enums\RateType;
use App\Enums\DeliveryTimeUnit;
use Illuminate\Support\Facades\Schema;
use App\Enums\AutoGenerateTimeSlotsUnit;
use App\Enums\DeliveryMethodFeeType;
use App\Enums\DeliveryMethodScheduleType;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('delivery_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->boolean('active')->default(0);
            $table->string('name', 40);
            $table->string('description', 500)->nullable();
            $table->char('currency', 3)->default(config('app.currency'));

            $table->boolean('qualify_on_minimum_grand_total')->default(false);
            $table->decimal('minimum_grand_total', 12, 3)->default(0);

            $table->boolean('offer_free_delivery_on_minimum_grand_total')->default(false);
            $table->decimal('free_delivery_minimum_grand_total', 12, 3)->default(0);
            $table->boolean('ask_for_an_address')->default(false);
            $table->boolean('pin_location_on_map')->default(false);
            $table->boolean('show_distance_on_invoice')->default(false);

            $table->boolean('charge_fee')->default(false);
            $table->enum('fee_type', DeliveryMethodFeeType::values())->default(DeliveryMethodFeeType::FLAT_FEE->value);
            $table->decimal('percentage_fee_rate', 5, 2)->default(0);
            $table->decimal('flat_fee_rate', 12, 3)->default(0);

            $table->json('weight_categories')->nullable();
            $table->json('distance_zones')->nullable();
            $table->json('postal_code_zones')->nullable();

            $table->enum('fallback_fee_type', RateType::values())->default(RateType::FLAT->value);
            $table->decimal('fallback_percentage_fee_rate', 5, 2)->default(0);
            $table->decimal('fallback_flat_fee_rate', 12, 3)->default(0);

            $table->boolean('set_schedule')->default(false);
            $table->enum('schedule_type', DeliveryMethodScheduleType::values())->default(DeliveryMethodScheduleType::DATE->value);
            $table->json('operational_hours')->nullable();
            $table->boolean('auto_generate_time_slots')->default(false);
            $table->unsignedTinyInteger('time_slot_interval_value')->default(1);
            $table->enum('time_slot_interval_unit', AutoGenerateTimeSlotsUnit::values())->default(AutoGenerateTimeSlotsUnit::HOUR->value);

            $table->boolean('same_day_delivery')->default(false);
            $table->boolean('require_minimum_notice_for_orders')->default(false);
            $table->unsignedTinyInteger('earliest_delivery_time_value')->default(1);
            $table->enum('earliest_delivery_time_unit', DeliveryTimeUnit::values())->default(DeliveryTimeUnit::DAY->value);
            $table->boolean('restrict_maximum_notice_for_orders')->default(false);
            $table->unsignedTinyInteger('latest_delivery_time_value')->default(1);

            $table->boolean('set_daily_order_limit')->default(false);
            $table->unsignedMediumInteger('daily_order_limit')->default(100);

            $table->boolean('capture_additional_fields')->default(false);
            $table->json('additional_fields')->nullable();

            $table->unsignedTinyInteger('position')->nullable();
            $table->foreignUuid('store_id')->nullable();
            $table->timestamps();

            $table->index('name');
            $table->index('store_id');

            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_methods');
    }
};
