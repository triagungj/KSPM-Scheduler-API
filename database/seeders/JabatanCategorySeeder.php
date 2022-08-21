<?php

namespace Database\Seeders;

use App\Models\JabatanCategory;
use Illuminate\Database\Seeder;

class JabatanCategorySeeder extends Seeder
{

    public function run()
    {
        JabatanCategory::create([
            'name' => 'Pengurus Inti'
        ]);

        JabatanCategory::create([
            'name' => 'Staff'
        ]);

        JabatanCategory::create([
            'name' => 'Anggota'
        ]);
    }
}
