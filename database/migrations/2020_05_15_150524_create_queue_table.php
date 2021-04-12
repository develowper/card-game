<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQueueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('queue', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->string('chat_id');
            $table->string('chat_type');
            $table->string('chat_username');
            $table->boolean('is_vip')->default(false);
            $table->string('chat_title')->nullable();
            $table->string('chat_description', 250)->nullable();
            $table->string('chat_main_color', 50)->nullable();
            $table->integer('show_time');//min


            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('queue');
    }
}
