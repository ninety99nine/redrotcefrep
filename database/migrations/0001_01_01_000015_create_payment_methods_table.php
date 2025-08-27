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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->boolean('active')->default(0);
            $table->string('name', 40);
            $table->string('type', 40);
            $table->boolean('automated_verification')->default(0);
            $table->json('currencies')->nullable();
            $table->json('countries')->nullable();
            $table->json('allowed_countries')->nullable();
            $table->json('ussd_codes')->nullable();
            $table->json('config_schema')->nullable();
            $table->unsignedTinyInteger('position')->nullable();
            $table->timestamps();

            $table->index('name');
            $table->index('type');
            $table->index('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
