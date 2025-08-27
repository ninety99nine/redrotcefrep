<?php

use App\Enums\DesignCardType;
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
        Schema::create('design_cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->boolean('visible')->default(0);
            $table->enum('type', DesignCardType::values());
            $table->unsignedTinyInteger('position')->nullable();
            $table->json('metadata');
            $table->uuid('store_id');
            $table->timestamps();

            $table->index('store_id');
            $table->index('position');

            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_cards');
    }
};
