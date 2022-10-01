<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('id_ID');

        for($i = 0; $i < 25; $i++){
            News::create([
            'id' => Str::uuid(),
            'title' => $faker->text(20),
            'description' => $faker->text(200),
        ]);
        }
    }
}
