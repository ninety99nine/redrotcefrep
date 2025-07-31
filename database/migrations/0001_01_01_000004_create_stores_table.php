<?php

use App\Enums\TaxMethod;
use App\Enums\WeightUnit;
use App\Enums\DistanceUnit;
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
        Schema::create('stores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('alias')->unique();
            $table->string('email')->nullable();
            $table->string('ussd_mobile_number', 20)->nullable();
            $table->string('contact_mobile_number', 20)->nullable();
            $table->string('whatsapp_mobile_number', 20)->nullable();
            $table->string('call_to_action')->default('Order');
            $table->string('description', 120)->nullable();
            $table->string('qr_code_file_path')->nullable();
            $table->boolean('offer_rewards')->default(false);
            $table->decimal('reward_percentage_rate', 5, 2)->default(0);
            $table->json('social_media_links')->nullable();
            $table->char('country', 2)->default(config('app.country'));
            $table->char('currency', 3)->default(config('app.currency'));
            $table->char('language', 2)->default(strtolower(config('app.language')));
            $table->enum('distance_unit', DistanceUnit::values())->default(DistanceUnit::KM->value);
            $table->enum('weight_unit', WeightUnit::values())->default(WeightUnit::KILOGRAM->value);
            $table->enum('tax_method', TaxMethod::values())->default(TaxMethod::INCLUSIVE->value);
            $table->decimal('tax_percentage_rate', 5, 2)->default(0);
            $table->string('tax_id', 50)->nullable();
            $table->boolean('show_opening_hours')->default(false);
            $table->boolean('allow_checkout_on_closed_hours')->default(true);
            $table->json('opening_hours')->nullable();
            $table->boolean('online')->default(true);
            $table->string('offline_message', 120)->default('We are currently offline');
            $table->string('sms_sender_name', 11)->nullable();
            $table->string('customer_section_heading', 25)->nullable();
            $table->boolean('show_customer_email')->default(false);
            $table->boolean('show_customer_last_name')->default(true);
            $table->boolean('show_customer_first_name')->default(true);
            $table->boolean('customer_email_required')->default(false);
            $table->boolean('customer_last_name_required')->default(false);
            $table->boolean('customer_first_name_required')->default(false);
            $table->boolean('show_items')->default(true);
            $table->string('items_section_heading', 25)->nullable();
            $table->boolean('show_delivery_methods')->default(true);
            $table->string('delivery_methods_section_heading', 25)->nullable();
            $table->string('delivery_schedule_title', 25)->nullable();
            $table->string('delivery_address_title', 25)->nullable();
            $table->boolean('show_tips')->default(true);
            $table->string('tip_section_heading', 25)->nullable();
            $table->json('tips')->nullable();
            $table->boolean('show_specify_tip')->default(true);
            $table->boolean('show_promotions')->default(true);
            $table->string('promotions_section_heading', 25)->nullable();
            $table->string('cost_breakdown_section_heading', 25)->nullable();
            $table->boolean('combine_fees_into_one_amount')->default(false);
            $table->boolean('combine_discounts_into_one_amount')->default(false);
            $table->json('checkout_fees')->nullable();
            $table->unsignedInteger('order_number_padding')->default(2);
            $table->unsignedInteger('order_number_counter')->default(0);
            $table->string('order_number_prefix')->nullable();
            $table->string('order_number_suffix')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('name');
            $table->index('alias');
            $table->index('created_at');
            $table->index('ussd_mobile_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
