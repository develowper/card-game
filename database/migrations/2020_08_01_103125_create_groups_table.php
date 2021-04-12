<?php

use App\Group;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 20);
            $table->string('emoji', 20);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });

        Group::truncate();
        DB::table('groups')->insert([
            ['id' => 0, 'name' => 'آزاد', 'emoji' => '❓'],
            ['id' => 1, 'name' => 'کسب-و-کار', 'emoji' => '💸'],
            ['id' => 2, 'name' => 'سرگرمی', 'emoji' => '🎪'],
            ['id' => 3, 'name' => 'ورزشی', 'emoji' => '⚽'],
            ['id' => 4, 'name' => 'ادبیات', 'emoji' => '🎭'],
            ['id' => 5, 'name' => 'هنری', 'emoji' => '🎨'],
            ['id' => 6, 'name' => 'خبری', 'emoji' => '📡'],
            ['id' => 7, 'name' => 'فیلم-موسیقی', 'emoji' => '🔊'],
            ['id' => 8, 'name' => 'تصویر', 'emoji' => '📷'],
            ['id' => 9, 'name' => 'علمی', 'emoji' => '🔭'],
            ['id' => 10, 'name' => 'آموزشی', 'emoji' => '🎓'],
            ['id' => 11, 'name' => 'مذهبی', 'emoji' => '🙏'],


        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('groups');
    }
}
