<?php

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 50)->nullable()->unique();
            $table->string('telegram_username', 50)->nullable()->unique();
            $table->string('telegram_id', 50)->nullable()->unique();
            $table->string('img', 100)->nullable();
            $table->string('role', 2)->nullable()->default("us");
            $table->string('password', 255)->nullable();
            $table->string('token')->default(bin2hex(openssl_random_pseudo_bytes(30)));
            $table->integer('limits')->default(1);
            $table->string('channels')->nullable()->default('[]');
            $table->string('groups')->nullable()->default('[]');
            $table->string('must_join')->nullable()->default('[]');
            $table->integer('score')->default(0);
            $table->smallInteger('step')->nullable()->default(0);
            $table->boolean('active')->default(true);
//            $table->softDeletes();
            $table->rememberToken();

            $table->dateTime('expires_at')->nullable()->default(null);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();


        });
        User::truncate();
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'admin', 'telegram_username' => '@Develowper', 'telegram_id' => '72534783', 'img' => 'https://vartashop.ir/wp-content/uploads/2020/03/vartashop_logo-300x300.png',
                'role' => 'ad', 'limits' => 10000000, 'password' => Hash::make('o7615564351'),
            ],

        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::dropIfExists('banner_types');
        Schema::dropIfExists('users');
    }
}
