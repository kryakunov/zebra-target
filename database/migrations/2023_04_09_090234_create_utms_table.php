<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUtmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('utms', function (Blueprint $table) {
            $table->id();
            $table->integer('vk_id');
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();;
            $table->string('utm_campaign')->nullable();;
            $table->string('utm_content')->nullable();;
            $table->string('utm_term')->nullable();;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('utms');
    }
}
