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
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('user_content');
            $table->text('assistant_content')->nullable();
            $table->integer('prompt_tokens')->default(0);
            $table->integer('completion_tokens')->default(0);
            $table->integer('total_tokens')->default(0);
            $table->timestamp('request_at')->nullable();
            $table->timestamp('response_at')->nullable();
            $table->foreignUuid('ai_lesson_id')->nullable();
            $table->foreignUuid('ai_assistant_id');
            $table->foreignUuid('user_id');
            $table->timestamps();

            /* Add Indexes */
            $table->index('user_id');
            $table->index('ai_lesson_id');
            $table->index('ai_assistant_id');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('ai_lesson_id')->references('id')->on('ai_lessons')->nullOnDelete();
            $table->foreign('ai_assistant_id')->references('id')->on('ai_assistants')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ai_messages');
    }
};
