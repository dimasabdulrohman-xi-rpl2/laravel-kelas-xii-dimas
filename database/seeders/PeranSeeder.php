<?php

namespace Database\Seeders;

use App\Models\Cast;
use App\Models\Film;
use App\Models\Peran;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class PeranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Peran::query()->exists()) {
            return;
        }

        $films = Film::query()->orderBy('id')->get();
        $casts = Cast::query()->orderBy('id')->get();
        if ($films->isEmpty() || $casts->isEmpty()) {
            return;
        }

        $faker = Faker::create('id_ID');
        foreach ($films as $film) {
            foreach ($casts->random(min(3, $casts->count())) as $cast) {
                Peran::create([
                    'film_id' => $film->id,
                    'cast_id' => $cast->id,
                    'actor' => $faker->jobTitle,
                ]);
            }
        }
    }
}
