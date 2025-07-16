<?php

use App\Enums\TransactionPaymentStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Enums\TransactionVerificationType;
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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('payment_status', TransactionPaymentStatus::values());
            $table->string('failure_type')->nullable();
            $table->string('failure_reason')->nullable();
            $table->string('description')->nullable();
            $table->char('currency', 3)->default(config('app.currency'));
            $table->decimal('amount', 12, 3)->default(0);
            $table->unsignedTinyInteger('percentage')->default(100);
            $table->json('metadata')->nullable();
            $table->foreignUuid('requested_by_user_id')->nullable();
            $table->enum('verification_type', TransactionVerificationType::values());
            $table->foreignUuid('manually_verified_by_user_id')->nullable();
            $table->foreignUuid('payment_method_id')->nullable();
            $table->boolean('created_using_auto_billing')->default(0);
            $table->foreignUuid('customer_id')->nullable();
            $table->foreignUuid('store_id')->nullable();
            $table->foreignUuid('ai_assistant_id')->nullable();
            $table->uuidMorphs('owner');
            $table->timestamps();

            $table->index('customer_id');
            $table->index('payment_status');
            $table->index('ai_assistant_id');
            $table->index('requested_by_user_id');
            $table->index('manually_verified_by_user_id');

            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('requested_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('ai_assistant_id')->references('id')->on('ai_assistants')->nullOnDelete();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->nullOnDelete();
            $table->foreign('manually_verified_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
