<?php

namespace Database\Seeders;

use App\Models\Enum\DayEnum;
use App\Models\Enum\StatusEnum;
use App\Models\Jabatan;
use App\Models\Partisipan;
use App\Models\Pertemuan;
use App\Models\Petugas;
use App\Models\Schedule;
use App\Models\ScheduleCandidate;
use App\Models\ScheduleRequest;
use App\Models\Sesi;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SampleSeeder extends Seeder
{
    function addMasterDataPertemuan()
    {
        DB::table('schedules')->delete();
        DB::table('schedule_candidates')->delete();
        DB::table('schedule_requests')->delete();
        DB::table('sesis')->delete();
        DB::table('pertemuans')->delete();

        $pertemuan1Uuid = Str::uuid();

        Pertemuan::create([
            'id' => $pertemuan1Uuid,
            'name' => 'Pertemuan Materi 1'
        ]);
        // SENIN
        Sesi::create([
            'id' => 1,
            'name' => 'Sesi 1',
            'pertemuan_id' => $pertemuan1Uuid,
            'hari' => DayEnum::Senin,
            'waktu' => '09:00 - 10:30'
        ]);

        Sesi::create([
            'id' => 2,
            'name' => 'Sesi 2',
            'pertemuan_id' => $pertemuan1Uuid,
            'hari' => DayEnum::Senin,
            'waktu' => '10:40 - 12:10'
        ]);


        $pertemuan2Uuid = Str::uuid();
        Pertemuan::create([
            'id' => $pertemuan2Uuid,
            'name' => 'Pertemuan Materi 2'
        ]);
        Sesi::create([
            'id' => 3,
            'name' => 'Sesi 1',
            'pertemuan_id' => $pertemuan2Uuid,
            'hari' => DayEnum::Selasa,
            'waktu' => '12:50 - 14:20'
        ]);
        Sesi::create([
            'id' => 4,
            'name' => 'Sesi 2',
            'pertemuan_id' => $pertemuan2Uuid,
            'hari' => DayEnum::Selasa,
            'waktu' => '14:30 - 16:00'
        ]);
    }

    function addPartisipan($username, $name, $jabatanId, $listSesiId)
    {
        $faker = Faker::create('id_ID');
        $partisipanId = Str::uuid();
        $requestId =  Str::uuid();

        User::create([
            'id' => Str::uuid(),
            'username' => $username,
            'password' => Hash::make('12345678'),
            'is_petugas' => false,
        ]);

        Partisipan::create([
            'id' => $partisipanId,
            'username' => $username,
            'name' => $name,
            'member_id' => $faker->numerify('P###########'),
            'phone_number' => $faker->numerify('628##########'),
            'jabatan_id' => $jabatanId,
        ]);

        ScheduleRequest::create([
            'id' => $requestId,
            'partisipan_id' => $partisipanId,
            'status' => StatusEnum::Accepted,
            'petugas_id' => Petugas::inRandomOrder()->first()->id
        ]);

        foreach ($listSesiId as $sesiId) {
            ScheduleCandidate::create([
                'id' => Str::uuid(),
                'schedule_request_id' => $requestId,
                'session_id' => $sesiId,
            ]);
        }
    }

    public function run()
    {
        $this->addMasterDataPertemuan();

        $this->addPartisipan(
            '5180411200',
            'Pengurus 1',
            Jabatan::where('name', 'Ketua Umum')->first()->id,
            [1, 2, 4]
        );
        $this->addPartisipan(
            '5180411202',
            'Pengurus 2',
            Jabatan::where('name', 'Bursa')->first()->id,
            [1, 3]
        );
        $this->addPartisipan(
            '5180411203',
            'Staff 1',
            Jabatan::where('name', 'Staff HRD')->first()->id,
            [1, 2, 4]
        );
        $this->addPartisipan(
            '5180411205',
            'Staff 2',
            Jabatan::where('name', 'Staff PRD')->first()->id,
            [1, 3]
        );
        $this->addPartisipan(
            '5180411206',
            'Staff 3',
            Jabatan::where('name', 'Staff Trading')->first()->id,
            [2, 3, 4]
        );
        $this->addPartisipan(
            '5180411207',
            'Staff 4',
            Jabatan::where('name', 'Staff Edukasi')->first()->id,
            [1, 2, 4]
        );

        $this->addPartisipan(
            '5190411202',
            'Anggota 1',
            Jabatan::where('name', 'Anggota Magang')->first()->id,
            [1, 2, 4]
        );
        $this->addPartisipan(
            '5190411205',
            'Anggota 2',
            Jabatan::where('name', 'Anggota Magang')->first()->id,
            [1, 2, 3, 4]
        );
        $this->addPartisipan(
            '5190111202',
            'Anggota 3',
            Jabatan::where('name', 'Anggota Magang')->first()->id,
            [2, 4]
        );
        $this->addPartisipan(
            '5190211203',
            'Anggota 4',
            Jabatan::where('name', 'Anggota Magang')->first()->id,
            [1, 3]
        );
        $this->addPartisipan(
            '5190111205',
            'Anggota 5',
            Jabatan::where('name', 'Anggota Magang')->first()->id,
            [1, 3, 4]
        );
        $this->addPartisipan(
            '5190211207',
            'Anggota 6',
            Jabatan::where('name', 'Anggota Magang')->first()->id,
            [1, 2, 4]
        );
    }
}
