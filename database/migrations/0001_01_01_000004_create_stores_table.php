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
            $table->string('description', 120)->nullable();
            $table->string('bg_color', 7)->nullable();
            $table->boolean('online')->default(true);
            $table->string('offline_message', 120)->default('We are currently offline');
            $table->string('alias')->unique();
            $table->string('email')->nullable();
            $table->string('sms_sender_name', 11)->nullable();
            $table->string('ussd_mobile_number', 20)->nullable();
            $table->string('whatsapp_mobile_number', 20)->nullable();
            $table->string('call_to_action')->default('Buy');
            $table->string('qr_code_file_path')->nullable();
            $table->boolean('offer_rewards')->default(false);
            $table->decimal('reward_percentage_rate', 5, 2)->default(0);
            $table->char('country', 2)->default(config('app.country'));
            $table->char('currency', 3)->default(config('app.currency'));
            $table->char('language', 2)->default(strtolower(config('app.language')));
            $table->enum('weight_unit', WeightUnit::values())->default(WeightUnit::KILOGRAM->value);
            $table->enum('distance_unit', DistanceUnit::values())->default(DistanceUnit::KM->value);
            $table->enum('tax_method', TaxMethod::values())->default(TaxMethod::INCLUSIVE->value);
            $table->decimal('tax_percentage_rate', 5, 2)->default(0);
            $table->string('tax_id', 50)->nullable();
            $table->boolean('show_opening_hours')->default(false);
            $table->boolean('allow_checkout_on_closed_hours')->default(true);
            $table->json('opening_hours')->nullable();

            $table->json('tips')->nullable();
            $table->json('checkout_fees')->nullable();
            $table->boolean('combine_fees')->default(false);
            $table->boolean('combine_discounts')->default(false);

            $table->unsignedInteger('order_number_padding')->default(2);
            $table->unsignedInteger('order_number_counter')->default(0);
            $table->string('order_number_prefix')->nullable();
            $table->string('order_number_suffix')->nullable();

            $table->string('message_footer')->nullable();
            $table->boolean('show_sms_channel')->default(false);
            $table->boolean('show_line_channel')->default(false);
            $table->boolean('skip_payment_page')->default(false);
            $table->boolean('show_whatsapp_channel')->default(true);
            $table->string('line_channel_username')->nullable();
            $table->boolean('show_telegram_channel')->default(false);
            $table->string('show_messenger_channel')->default(false);
            $table->string('telegram_channel_username')->nullable();
            $table->string('messenger_channel_username')->nullable();

            $table->boolean('invoice_show_logo')->default(true);
            $table->boolean('invoice_show_qr_code')->default(true);
            $table->string('invoice_header')->nullable();
            $table->string('invoice_footer')->nullable();
            $table->string('invoice_company_name')->nullable();
            $table->string('invoice_company_email')->nullable();
            $table->string('invoice_company_mobile_number')->nullable();

            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->json('seo_keywords')->nullable();
            $table->string('google_analytics_id')->nullable();
            $table->string('meta_pixel_id')->nullable();
            $table->string('tiktok_pixel_id')->nullable();

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
