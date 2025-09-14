<?php

use App\Enums\TaxMethod;
use App\Enums\OrderStatus;
use App\Enums\OrderPaymentStatus;
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
        Schema::create('orders', function (Blueprint $table) {

            $table->uuid('id')->primary();
            $table->string('number')->nullable();
            $table->string('summary')->nullable();
            $table->enum('status', OrderStatus::values())->default(OrderStatus::WAITING->value);
            $table->char('currency', 3)->default(config('app.currency'));
            $table->decimal('subtotal', 12, 3)->default(0);
            $table->decimal('discount_total', 12, 3)->default(0);
            $table->decimal('subtotal_after_discount', 12, 3)->default(0);
            $table->enum('vat_method', TaxMethod::values())->default(TaxMethod::INCLUSIVE->value);
            $table->decimal('vat_rate', 5, 2)->default(0);
            $table->decimal('vat_amount', 12, 3)->default(0);
            $table->decimal('fee_total', 12, 3)->default(0);
            $table->decimal('adjustment_total', 12, 3)->default(0);
            $table->decimal('grand_total', 12, 3)->default(0);

            $table->enum('payment_status', OrderPaymentStatus::values())->default(OrderPaymentStatus::UNPAID->value);
            $table->decimal('paid_total', 12, 3)->default(0);
            $table->unsignedTinyInteger('paid_percentage')->default(0);
            $table->decimal('outstanding_total', 12, 3)->default(0);
            $table->unsignedTinyInteger('outstanding_percentage')->default(100);

            $table->unsignedSmallInteger('total_products')->default(0);
            $table->unsignedSmallInteger('total_cancelled_products')->default(0);
            $table->unsignedSmallInteger('total_uncancelled_products')->default(0);
            $table->unsignedSmallInteger('total_product_quantities')->default(0);
            $table->unsignedSmallInteger('total_cancelled_product_quantities')->default(0);
            $table->unsignedSmallInteger('total_uncancelled_product_quantities')->default(0);

            $table->unsignedSmallInteger('total_promotions')->default(0);
            $table->unsignedSmallInteger('total_cancelled_promotions')->default(0);
            $table->unsignedSmallInteger('total_uncancelled_promotions')->default(0);
            $table->boolean('applied_promotion_code')->default(false);

            $table->string('delivery_method_name')->nullable();
            $table->boolean('free_delivery')->default(false);
            $table->date('delivery_date')->nullable();
            $table->string('delivery_timeslot')->nullable();
            $table->foreignUuid('delivery_method_id')->nullable();

            $table->float('delivery_distance_value')->nullable();
            $table->string('delivery_distance_unit')->nullable();
            $table->string('delivery_distance_text')->nullable();
            $table->float('delivery_duration_value')->nullable();
            $table->string('delivery_duration_text')->nullable();

            $table->float('delivery_weight_value')->nullable();
            $table->string('delivery_weight_unit')->nullable();
            $table->string('delivery_weight_text')->nullable();

            $table->foreignUuid('courier_id')->nullable();
            $table->string('tracking_number')->nullable();

            $table->char('collection_code', 6)->nullable();
            $table->string('collection_qr_code')->nullable();
            $table->timestamp('collection_code_expires_at')->nullable();
            $table->boolean('collection_verified')->default(false);
            $table->timestamp('collection_verified_at')->nullable();
            $table->foreignUuid('collection_verified_by_user_id')->nullable();
            $table->text('collection_note')->nullable();

            $table->string('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->string('customer_first_name')->nullable();
            $table->string('customer_last_name')->nullable();
            $table->string('customer_mobile_number', 20)->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_note')->nullable();
            $table->foreignUuid('customer_id')->nullable();
            $table->foreignUuid('placed_by_user_id')->nullable();

            $table->unsignedSmallInteger('total_views_by_team')->default(1);
            $table->timestamp('first_viewed_by_team_at')->nullable();
            $table->timestamp('last_viewed_by_team_at')->nullable();

            $table->text('internal_note')->nullable();
            $table->text('remark')->nullable();

            $table->foreignUuid('store_id');
            $table->foreignUuid('created_by_user_id')->nullable();
            $table->foreignUuid('assigned_to_user_id')->nullable();

            $table->timestamps();

            $table->index(['status']);
            $table->index(['created_at']);
            $table->index(['payment_status']);
            $table->index(['customer_first_name', 'customer_last_name']);

            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('courier_id')->references('id')->on('couriers')->nullOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('placed_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('assigned_to_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('delivery_method_id')->references('id')->on('delivery_methods')->nullOnDelete();
            $table->foreign('collection_verified_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
