<?php

namespace Database\Seeders;

use App\Models\Enum\StatusEnum;

use App\Models\Partisipan;
use App\Models\Petugas;
use App\Models\ScheduleCandidate;
use App\Models\ScheduleRequest;
use App\Models\Sesi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    function createUser($jabatanId, $listId)
    {
        $faker = Faker::create('id_ID');

        $partisipanId = Str::uuid();
        $requestId =  Str::uuid();
        $username = $faker->username;
        User::create([
            'id' => Str::uuid(),
            'username' => $username,
            'password' => Hash::make('12345678'),
            'is_petugas' => false,
        ]);

        Partisipan::create([
            'id' => $partisipanId,
            'username' => $username,
            'name' => $faker->name(),
            'member_id' => $faker->numerify('P###########'),
            'phone_number' => $faker->phoneNumber,
            'jabatan_id' => $jabatanId,
        ]);

        // $randomRequest = rand(true, false);
        ScheduleRequest::create([
            'id' => $requestId,
            'partisipan_id' => $partisipanId,
            'status' => StatusEnum::Accepted,
            // 'status' => ($randomRequest)
            //     ? StatusEnum::Requested
            //     : null,
        ]);

        foreach ($listId as $sesiId) {
            $randomSesi = rand(true, false);
            if ($randomSesi) {
                ScheduleCandidate::create([
                    'id' => Str::uuid(),
                    'schedule_request_id' => $requestId,
                    'session_id' => $sesiId,
                ]);
            }
        }
    }

    public function run()
    {
        User::create([
            'id' => Str::uuid(),
            'username' => 'triagungj',
            'password' => Hash::make('12345678'),
            'is_petugas' => true,
        ]);

        Petugas::create([
            'id' => Str::uuid(),
            'username' => 'triagungj',
            'name' => 'Tri Agung J',
            'phone_number' => '6282327495261',
        ]);

        User::create([
            'id' => Str::uuid(),
            'username' => 'validator',
            'password' => Hash::make('12345678'),
            'is_petugas' => true,
        ]);

        Petugas::create([
            'id' => Str::uuid(),
            'username' => 'validator',
            'name' => 'User Validator',
            'phone_number' => '6282327495261',
        ]);

        // $userUuid = Str::uuid();
        // User::create([
        //     'id' => Str::uuid(),
        //     'username' => 'faradhika',
        //     'password' => Hash::make('12345678'),
        //     'is_petugas' => false,
        // ]);

        // $jabatan = DB::table('jabatans')
        //     ->inRandomOrder()
        //     ->first();
        // Partisipan::create([
        //     'id' => $userUuid,
        //     'username' => 'faradhika',
        //     'name' => 'Faradhika M. D.',
        //     'member_id' => 'P1234215124213',
        //     'phone_number' => '6282327495261',
        //     'jabatan_id' => $jabatan->id,
        // ]);

        // ScheduleRequest::create([
        //     'id' => Str::uuid(),
        //     'partisipan_id' => $userUuid,
        // ]);

        $faker = Faker::create('id_ID');

        $listId = [];
        $listSesi = Sesi::all();
        foreach ($listSesi as $sesi) {
            array_push($listId, $sesi->id);
        }

        $anggota = $jabatan = DB::table('jabatans')
            ->where('name', '=', 'Anggota Magang')
            ->first();

        $ketum = $jabatan = DB::table('jabatans')
            ->where('name', '=', 'Ketua Umum')->first();
        $this->createUser($ketum->id, $listId);

        $ketuaEdukasi = $jabatan = DB::table('jabatans')
            ->where('name', '=', 'Ketua Edukasi')->first();
        $this->createUser($ketuaEdukasi->id, $listId);

        $ketuaTrading = $jabatan = DB::table('jabatans')
            ->where('name', '=', 'Ketua Trading')->first();
        $this->createUser($ketuaTrading->id, $listId);

        $ketuaHrd = $jabatan = DB::table('jabatans')
            ->where('name', '=', 'Ketua HRD')->first();
        $this->createUser($ketuaHrd->id, $listId);

        $ketumRnD = $jabatan = DB::table('jabatans')
            ->where('name', '=', 'Ketua RnD')->first();
        $this->createUser($ketumRnD->id, $listId);

        $sekum = $jabatan = DB::table('jabatans')
            ->where('name', '=', 'Sekretaris Umum')->first();
        for ($i = 0; $i < 2; $i++) {
            $this->createUser($sekum->id, $listId);
        }

        $bendum = $jabatan = DB::table('jabatans')
            ->where('name', '=', 'Bendahara Umum')->first();
        for ($i = 0; $i < 2; $i++) {
            $this->createUser($bendum->id, $listId);
        }

        $bursa = $jabatan = DB::table('jabatans')
            ->where('name', '=', 'Bursa')->first();
        $this->createUser($bursa->id, $listId);

        $staffEdukasi = $jabatan = DB::table('jabatans')
            ->where('name', '=', 'Staff Edukasi')->first();
        for ($i = 0; $i <= 5; $i++) {
            $this->createUser($staffEdukasi->id, $listId);
        }

        $staffRnD = $jabatan = DB::table('jabatans')
            ->where('name', '=', 'Staff RnD')->first();
        for ($i = 0; $i <= 5; $i++) {
            $this->createUser($staffRnD->id, $listId);
        }

        $staffHRD = $jabatan = DB::table('jabatans')
            ->where('name', '=', 'Staff HRD')->first();
        for ($i = 0; $i <= 5; $i++) {
            $this->createUser($staffHRD->id, $listId);
        }

        $staffTrading = $jabatan = DB::table('jabatans')
            ->where('name', '=', 'Staff Trading')->first();
        for ($i = 0; $i <= 5; $i++) {
            $this->createUser($staffTrading->id, $listId);
        }
        $staffPRD = $jabatan = DB::table('jabatans')
            ->where('name', '=', 'Staff PRD')->first();
        for ($i = 0; $i <= 5; $i++) {
            $this->createUser($staffPRD->id, $listId);
        }


        for ($i = 1; $i <= 50; $i++) {
            $partisipanId = Str::uuid();
            $requestId =  Str::uuid();
            $username = $faker->username;
            User::create([
                'id' => Str::uuid(),
                'username' => $username,
                'password' => Hash::make('12345678'),
                'is_petugas' => false,
            ]);

            Partisipan::create([
                'id' => $partisipanId,
                'username' => $username,
                'name' => $faker->name(),
                'member_id' => $faker->numerify('A###########'),
                'phone_number' => $faker->phoneNumber,
                'jabatan_id' => $anggota->id,
            ]);

            $randomRequest = rand(true, false);
            ScheduleRequest::create([
                'id' => $requestId,
                'partisipan_id' => $partisipanId,
                'status' => StatusEnum::Accepted
                // 'status' => ($randomRequest)
                //     ? StatusEnum::Requested
                //     : null,
            ]);

            foreach ($listId as $sesiId) {
                $randomSesi = rand(true, false);
                if ($randomSesi) {
                    ScheduleCandidate::create([
                        'id' => Str::uuid(),
                        'schedule_request_id' => $requestId,
                        'session_id' => $sesiId,
                    ]);
                }
            }
        }
    }
}
