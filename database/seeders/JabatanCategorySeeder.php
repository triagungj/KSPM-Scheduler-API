<?php

namespace Database\Seeders;

use App\Models\JabatanCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;


class JabatanCategorySeeder extends Seeder
{

    public function run()
    {
        JabatanCategory::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Pengurus Inti'
        ]);

        JabatanCategory::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Staff'
        ]);

        JabatanCategory::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Anggota'
        ]);
    }
}
