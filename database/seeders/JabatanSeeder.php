<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use App\Models\JabatanCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
        $pengurusInti = DB::table(
            'jabatan_categories'
        )->where('name', '=', 'Pengurus Inti')->first();
        $staff = DB::table(
            'jabatan_categories'
        )->where('name', '=', 'Staff')->first();
        $anggota = DB::table('jabatan_categories')->where('name', '=', 'Anggota')->first();

        Jabatan::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Ketua Umum',
            'jabatan_category_id' => $pengurusInti->id,
        ]);

        Jabatan::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Sekretaris Umum',
            'jabatan_category_id' => $pengurusInti->id,
        ]);

        Jabatan::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Bendahara Umum',
            'jabatan_category_id' => $pengurusInti->id,
        ]);
        Jabatan::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Bursa',
            'jabatan_category_id' => $pengurusInti->id,
        ]);

        Jabatan::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Ketua HRD',
            'jabatan_category_id' => $pengurusInti->id,
        ]);
        Jabatan::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Staff HRD',
            'jabatan_category_id' => $staff->id,
        ]);

        Jabatan::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Ketua RnD',
            'jabatan_category_id' => $pengurusInti->id,
        ]);

        Jabatan::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Staff RnD',
            'jabatan_category_id' => $staff->id,
        ]);


        Jabatan::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Ketua Trading',
            'jabatan_category_id' => $pengurusInti->id,
        ]);
        Jabatan::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Staff Trading',
            'jabatan_category_id' => $staff->id,
        ]);

        Jabatan::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Ketua Edukasi',
            'jabatan_category_id' => $pengurusInti->id,
        ]);
        Jabatan::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Staff Edukasi',
            'jabatan_category_id' => $staff->id,
        ]);

        Jabatan::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Anggota Magang',
            'jabatan_category_id' =>
            $anggota->id,
        ]);
    }
}
