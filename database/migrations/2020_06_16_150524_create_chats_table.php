<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->string('user_telegram_id', 25)->index();
            $table->string('chat_id', 30)->index();
            $table->string('message_id', 30)->index()->nullable();
            $table->string('chat_type', 10);
            $table->string('chat_main_color', 10)->nullable();
            $table->string('chat_username', 50)->nullable();;
            $table->string('chat_title', 50)->nullable();
            $table->boolean('active')->default(true);
            $table->string('chat_description', 255)->nullable();


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
        Schema::dropIfExists('chats');
    }
}
