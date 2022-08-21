<?php

namespace Database\Seeders;

use App\Models\JabatanCategory;
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
        $this->call([
            JabatanCategorySeeder::class,
            JabatanSeeder::class,
            // UserSeeder::class,
            PertemuanSeeder::class,
            SesiSeeder::class,
        ]);
    }
}
