<?php

use Faker\Generator as Faker;
use App\ImdbMovie;

$factory->define(ImdbMovie::class, function (Faker $faker) {
    do {
        $title = $faker->words(4, true);
    } while (ImdbMovie::where('title', $title)->exists());
    return [
        'title' => $title,
        'title_of_movie' => $faker->words(4, true),
        'main_picture' => $faker->url(),
        'rate' => (string)(mt_rand(0, 100) / 10),
        'summary' => $faker->sentence(1)
    ];
});
