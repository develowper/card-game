<?php

use App\Logo;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Logo::truncate();
        factory(Logo::class, 30)->create();
    }
}
