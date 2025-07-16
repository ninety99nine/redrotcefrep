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
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile_number', 20)->nullable();
            $table->date('birthday')->nullable();
            $table->text('notes')->nullable();
            $table->char('currency', 3)->default(config('app.currency'));
            $table->foreignUuid('store_id')->cascadeOnDelete();
            $table->timestamp('last_order_at')->nullable();
            $table->unsignedInteger('total_orders')->default(0);
            $table->decimal('total_spend', 12, 3)->default(0);
            $table->decimal('total_average_spend', 12, 3)->default(0);
            $table->timestamps();

            $table->index(['first_name', 'last_name']);
            $table->index('mobile_number');
            $table->index('email');

            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
};
