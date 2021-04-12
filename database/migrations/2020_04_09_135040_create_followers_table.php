<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFollowersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('followers', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->string('telegram_id', 25)->index();
            $table->string('added_by', 25)->index();
            $table->string('chat_id', 25)->index();
            $table->string('chat_username', 50)->index();

            $table->boolean('left')->default(false);
            $table->boolean('in_vip')->default(false);
            $table->smallInteger('follow_score')->unsigned()->nullable();

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
        Schema::dropIfExists('followers');
    }
}
