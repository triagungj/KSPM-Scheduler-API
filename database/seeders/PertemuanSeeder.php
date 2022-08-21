<?php

namespace Database\Seeders;

use App\Models\Pertemuan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PertemuanSeeder extends Seeder
{

    public function run()
    {
        Pertemuan::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Pertemuan Materi 1'
        ]);

        Pertemuan::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Pertemuan Materi 2'
        ]);
    }
}
