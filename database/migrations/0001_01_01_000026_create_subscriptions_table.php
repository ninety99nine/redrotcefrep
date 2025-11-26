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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->boolean('cancelled')->default('0');
            $table->foreignUuid('user_id')->nullable();
            $table->foreignUuid('transaction_id')->nullable();
            $table->foreignUuid('pricing_plan_id')->nullable();
            $table->uuidMorphs('owner');
            $table->timestamps();

            $table->index('user_id');
            $table->index('transaction_id');
            $table->index('pricing_plan_id');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('transaction_id')->references('id')->on('transactions')->nullOnDelete();
            $table->foreign('pricing_plan_id')->references('id')->on('pricing_plans')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
};
