<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Logo;
use App\User;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Logo::class, function (Faker $faker) {

    return [
        'name' => $faker->name,
        'path' => $faker->imageUrl(),
        'help' => [$faker->sentence(10), $faker->sentence(12)],

    ];
});

