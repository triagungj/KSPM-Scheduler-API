<?php

namespace Database\Seeders;

use App\Models\Pertemuan;
use Illuminate\Database\Seeder;

class PertemuanSeeder extends Seeder
{

    public function run()
    {
        Pertemuan::create([
            'name' => 'Pertemuan Materi 1'
        ]);

        Pertemuan::create([
            'name' => 'Pertemuan Materi 2'
        ]);
    }
}
