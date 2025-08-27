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
        Schema::create('ai_lessons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('prompt');
            $table->boolean('visible')->default(true);
            $table->unsignedTinyInteger('position')->nullable();
            $table->foreignUuid('ai_topic_id');
            $table->timestamps();

            /* Add Indexes */
            $table->index('position');
            $table->index('ai_topic_id');

            $table->foreign('ai_topic_id')->references('id')->on('ai_topics')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ai_lessons');
    }
};
