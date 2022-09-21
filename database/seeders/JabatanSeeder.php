<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use App\Models\JabatanCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pengurusIntiUuid = Str::uuid();
        JabatanCategory::create([
            'id' => $pengurusIntiUuid,
            'name' => 'Pengurus Inti',
        ]);
        Jabatan::create([
            'id' => Str::uuid(),
            'name' => 'Ketua Umum',
            'jabatan_category_id' => $pengurusIntiUuid,
        ]);
        Jabatan::create([
            'id' => Str::uuid(),
            'name' => 'Sekretaris Umum',
            'jabatan_category_id' => $pengurusIntiUuid,
        ]);
        Jabatan::create([
            'id' => Str::uuid(),
            'name' => 'Bendahara Umum',
            'jabatan_category_id' => $pengurusIntiUuid,
        ]);
        Jabatan::create([
            'id' => Str::uuid(),
            'name' => 'Bursa',
            'jabatan_category_id' => $pengurusIntiUuid,
        ]);
        Jabatan::create([
            'id' => Str::uuid(),
            'name' => 'Ketua HRD',
            'jabatan_category_id' => $pengurusIntiUuid,
        ]);
        Jabatan::create([
            'id' => Str::uuid(),
            'name' => 'Ketua RnD',
            'jabatan_category_id' => $pengurusIntiUuid,
        ]);
        Jabatan::create([
            'id' => Str::uuid(),
            'name' => 'Ketua Trading',
            'jabatan_category_id' => $pengurusIntiUuid,
        ]);
        Jabatan::create([
            'id' => Str::uuid(),
            'name' => 'Ketua Edukasi',
            'jabatan_category_id' => $pengurusIntiUuid,
        ]);
        Jabatan::create([
            'id' => Str::uuid(),
            'name' => 'Ketua PRD',
            'jabatan_category_id' => $pengurusIntiUuid,
        ]);

        $staffUuid = Str::uuid();
        JabatanCategory::create([
            'id' => $staffUuid,
            'name' => 'Staff',
        ]);
        Jabatan::create([
            'id' => Str::uuid(),
            'name' => 'Staff HRD',
            'jabatan_category_id' => $staffUuid,
        ]);
        Jabatan::create([
            'id' => Str::uuid(),
            'name' => 'Staff RnD',
            'jabatan_category_id' => $staffUuid,
        ]);
        Jabatan::create([
            'id' => Str::uuid(),
            'name' => 'Staff Trading',
            'jabatan_category_id' => $staffUuid,
        ]);
        Jabatan::create([
            'id' => Str::uuid(),
            'name' => 'Staff Edukasi',
            'jabatan_category_id' => $staffUuid,
        ]);
        Jabatan::create([
            'id' => Str::uuid(),
            'name' => 'Staff PRD',
            'jabatan_category_id' => $staffUuid,
        ]);

        $anggotaUuid = Str::uuid();
        JabatanCategory::create([
            'id' => $anggotaUuid,
            'name' => 'Anggota',
        ]);
        Jabatan::create([
            'id' => Str::uuid(),
            'name' => 'Anggota Magang',
            'jabatan_category_id' => $anggotaUuid,
        ]);
    }
}
