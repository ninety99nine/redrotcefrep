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
        Schema::create('ai_topics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('system_prompt');
            $table->boolean('visible')->default(true);
            $table->unsignedTinyInteger('position')->nullable();
            $table->timestamps();

            $table->index('position');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ai_topics');
    }
};
