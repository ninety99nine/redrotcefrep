<?php

use App\Enums\WorkflowTarget;
use App\Enums\WorkflowTrigger;
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
        Schema::create('workflows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->boolean('active')->default(0);
            $table->string('name');
            $table->enum('target', WorkflowTarget::values());
            $table->enum('trigger', WorkflowTrigger::values());
            $table->json('actions')->nullable();
            $table->unsignedTinyInteger('position')->nullable();
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
        Schema::dropIfExists('workflows');
    }
};
