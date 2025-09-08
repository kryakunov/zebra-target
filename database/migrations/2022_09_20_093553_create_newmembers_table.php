<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewmembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('newmembers', function (Blueprint $table) {
            $table->id();

            $table->integer('vk_id');
            $table->integer('status')->default(0);
            $table->integer('new_members')->default(0);
            $table->integer('track_id');

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
        Schema::dropIfExists('newmembers');
    }
}
