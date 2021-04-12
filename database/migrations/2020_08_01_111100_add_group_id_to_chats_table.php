<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGroupIdToChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chats', function (Blueprint $table) {

            $table->integer('group_id')->unsigned()->nullable()->after('chat_id');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chats', function (Blueprint $table) {
            // 1. Drop foreign key constraints
            $table->dropForeign(['group_id']);

            // 2. Drop the column
            $table->dropColumn('group_id');
        });
    }
}
