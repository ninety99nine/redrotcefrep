<?php

use App\Enums\DomainType;
use App\Enums\DomainStatus;
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
        Schema::create('domains', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');
            $table->string('name');
            $table->enum('type', DomainType::values());
            $table->enum('status', DomainStatus::values())->default(DomainStatus::PENDING->value);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_verification_attempt_at')->nullable();
            $table->timestamps();

            $table->index('store_id');
            $table->index('status');
            $table->unique(['store_id', 'name']);

            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
