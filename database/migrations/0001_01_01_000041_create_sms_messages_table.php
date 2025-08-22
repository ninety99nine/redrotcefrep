<?php

use App\Enums\SmsStatus;
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
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('status', SmsStatus::values());
            $table->string('failure_type')->nullable();
            $table->string('failure_reason')->nullable();
            $table->string('content');
            $table->json('metadata');
            $table->foreignUuid('store_id')->nullable();
            $table->string('sender_name');
            $table->string('sender_mobile_number', 20);
            $table->string('recipient_mobile_number', 20);
            $table->timestamps();

            /* Add Indexes */
            $table->index('store_id');

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
        Schema::dropIfExists('sms_messages');
    }
};
