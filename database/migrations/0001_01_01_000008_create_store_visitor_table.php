<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_visitor', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');
            $table->uuid('user_id')->nullable(); // For authenticated users
            $table->string('guest_id')->nullable(); // For unauthenticated users
            $table->unique(['store_id', 'user_id', 'guest_id'], 'store_visitor_unique'); // Unique index
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->nullable();
            $table->timestamp('last_visited_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_visitor');
    }
};
