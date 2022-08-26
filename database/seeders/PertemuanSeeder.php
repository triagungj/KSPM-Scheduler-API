<?php

namespace Database\Seeders;

use App\Models\Enum\DayEnum;
use App\Models\Pertemuan;
use App\Models\Sesi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PertemuanSeeder extends Seeder
{

    public function run()
    {
        $pertemuan1Uuid = Str::uuid();

        Pertemuan::create([
            'id' => $pertemuan1Uuid,
            'name' => 'Pertemuan Materi 1'
        ]);
        // SENIN
        Sesi::create([
            'name' => 'Sesi 1',
            'pertemuan_id' => $pertemuan1Uuid,
            'hari' => DayEnum::Senin,
            'waktu' => '09:00 - 10.30'
        ]);

        Sesi::create([
            'name' => 'Sesi 2',
            'pertemuan_id' => $pertemuan1Uuid,
            'hari' => DayEnum::Senin,
            'waktu' => '10:40 - 12.10'
        ]);

        Sesi::create([
            'name' => 'Sesi 3',
            'pertemuan_id' => $pertemuan1Uuid,
            'hari' => DayEnum::Senin,
            'waktu' => '12:50 - 14.20'
        ]);
        Sesi::create([
            'name' => 'Sesi 4',
            'pertemuan_id' => $pertemuan1Uuid,
            'hari' => DayEnum::Senin,
            'waktu' => '14:30 - 16.00'
        ]);

        // SELASA
        Sesi::create([
            'name' => 'Sesi 1',
            'pertemuan_id' => $pertemuan1Uuid,
            'hari' => DayEnum::Selasa,
            'waktu' => '09:00 - 10.30'
        ]);

        Sesi::create([
            'name' => 'Sesi 2',
            'pertemuan_id' => $pertemuan1Uuid,
            'hari' => DayEnum::Selasa,
            'waktu' => '10:40 - 12.10'
        ]);

        Sesi::create([
            'name' => 'Sesi 3',
            'pertemuan_id' => $pertemuan1Uuid,
            'hari' => DayEnum::Selasa,
            'waktu' => '12:50 - 14.20'
        ]);
        Sesi::create([
            'name' => 'Sesi 4',
            'pertemuan_id' => $pertemuan1Uuid,
            'hari' => DayEnum::Selasa,
            'waktu' => '14:30 - 16.00'
        ]);

        // RABU
        Sesi::create([
            'name' => 'Sesi 1',
            'pertemuan_id' => $pertemuan1Uuid,
            'hari' => DayEnum::Rabu,
            'waktu' => '09:00 - 10.30'
        ]);
        Sesi::create([
            'name' => 'Sesi 2',
            'pertemuan_id' => $pertemuan1Uuid,
            'hari' => DayEnum::Rabu,
            'waktu' => '10:40 - 12.10'
        ]);


        $pertemuan2Uuid = Str::uuid();
        Pertemuan::create([
            'id' => $pertemuan2Uuid,
            'name' => 'Pertemuan Materi 2'
        ]);
        Sesi::create([
            'name' => 'Sesi 3',
            'pertemuan_id' => $pertemuan2Uuid,
            'hari' => DayEnum::Rabu,
            'waktu' => '12:50 - 14.20'
        ]);
        Sesi::create([
            'name' => 'Sesi 4',
            'pertemuan_id' => $pertemuan2Uuid,
            'hari' => DayEnum::Rabu,
            'waktu' => '14:30 - 16.00'
        ]);

        // KAMIS
        Sesi::create([
            'name' => 'Sesi 1',
            'pertemuan_id' => $pertemuan2Uuid,
            'hari' => DayEnum::Kamis,
            'waktu' => '09:00 - 10.30'
        ]);

        Sesi::create([
            'name' => 'Sesi 2',
            'pertemuan_id' => $pertemuan2Uuid,
            'hari' => DayEnum::Kamis,
            'waktu' => '10:40 - 12.10'
        ]);

        Sesi::create([
            'name' => 'Sesi 3',
            'pertemuan_id' => $pertemuan2Uuid,
            'hari' => DayEnum::Kamis,
            'waktu' => '12:50 - 14.20'
        ]);
        Sesi::create([
            'name' => 'Sesi 4',
            'pertemuan_id' => $pertemuan2Uuid,
            'hari' => DayEnum::Kamis,
            'waktu' => '14:30 - 16.00'
        ]);

        // JUMAT
        Sesi::create([
            'name' => 'Sesi 1',
            'pertemuan_id' => $pertemuan2Uuid,
            'hari' => DayEnum::Jumat,
            'waktu' => '08:30 - 10.00'
        ]);

        Sesi::create([
            'name' => 'Sesi 2',
            'pertemuan_id' => $pertemuan2Uuid,
            'hari' => DayEnum::Jumat,
            'waktu' => '10:10 - 11.40'
        ]);

        Sesi::create([
            'name' => 'Sesi 3',
            'pertemuan_id' => $pertemuan2Uuid,
            'hari' => DayEnum::Jumat,
            'waktu' => '12:50 - 14.20'
        ]);
        Sesi::create([
            'name' => 'Sesi 4',
            'pertemuan_id' => $pertemuan2Uuid,
            'hari' => DayEnum::Jumat,
            'waktu' => '14:30 - 16.00'
        ]);
    }
}
