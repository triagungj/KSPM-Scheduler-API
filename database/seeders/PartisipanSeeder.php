<?php

namespace Database\Seeders;

use App\Models\Enum\StatusEnum;
use App\Models\Partisipan;
use App\Models\ScheduleCandidate;
use App\Models\ScheduleRequest;
use App\Models\Sesi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PartisipanSeeder extends Seeder
{
    function createUser($jabatanId, $listId, $randomRate, $username)
    {
        $faker = Faker::create('id_ID');

        $partisipanId = Str::uuid();
        $requestId =  Str::uuid();
        // $username = $faker->username;
        $username = $username;

        User::create([
            'id' => Str::uuid(),
            'username' => $username,
            'password' => Hash::make('12345678'),
            'is_petugas' => false,
        ]);

        Partisipan::create([
            'id' => $partisipanId,
            'username' => $username,
            'name' => $username,
            // 'name' => $faker->name(),
            'member_id' => $faker->numerify('P###########'),
            'phone_number' => $faker->phoneNumber,
            'jabatan_id' => $jabatanId,
        ]);

        ScheduleRequest::create([
            'id' => $requestId,
            'partisipan_id' => $partisipanId,
            'status' => StatusEnum::Accepted,
            // 'status' => ($randomRequest)
            //     ? StatusEnum::Requested
            //     : null,
        ]);

        foreach ($listId as $sesiId) {

            $randomRequest = rand(0, 100) / 100 <= $randomRate;
            if ($randomRequest) {
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
        $randomRate = 0.5;

        $listId = [];
        $listSesi = Sesi::all();
        foreach ($listSesi as $sesi) {
            array_push($listId, $sesi->id);
        }

        $ketum = DB::table('jabatans')
            ->where('name', '=', 'Ketua Umum')->first();
        $this->createUser($ketum->id, $listId, $randomRate, 'ketua_umum');

        $ketuaEdukasi = DB::table('jabatans')
            ->where('name', '=', 'Ketua Edukasi')->first();
        $this->createUser($ketuaEdukasi->id, $listId, $randomRate, 'ketua_edukasi');

        $ketuaTrading = DB::table('jabatans')
            ->where('name', '=', 'Ketua Trading')->first();
        $this->createUser($ketuaTrading->id, $listId, $randomRate, 'ketua_trading');

        $ketuaHrd = DB::table('jabatans')
            ->where('name', '=', 'Ketua HRD')->first();
        $this->createUser($ketuaHrd->id, $listId, $randomRate, 'ketua_hrd');

        $ketumRnD = DB::table('jabatans')
            ->where('name', '=', 'Ketua RnD')->first();
        $this->createUser($ketumRnD->id, $listId, $randomRate, 'ketua_rnd');

        $sekum = DB::table('jabatans')
            ->where('name', '=', 'Sekretaris Umum')->first();
        for ($i = 1; $i <= 2; $i++) {
            $this->createUser($sekum->id, $listId, $randomRate, 'sekum' . $i);
        }

        $bendum = DB::table('jabatans')
            ->where('name', '=', 'Bendahara Umum')->first();
        for ($i = 1; $i <= 2; $i++) {
            $this->createUser($bendum->id, $listId, $randomRate, 'bendum' . $i);
        }

        $bursa = DB::table('jabatans')
            ->where('name', '=', 'Bursa')->first();
        $this->createUser($bursa->id, $listId, $randomRate, 'bursa');

        $staffEdukasi = DB::table('jabatans')
            ->where('name', '=', 'Staff Edukasi')->first();
        for ($i = 1; $i <= 6; $i++) {
            $this->createUser($staffEdukasi->id, $listId, $randomRate, 'staff_edukasi' . $i);
        }

        $staffRnD = DB::table('jabatans')
            ->where('name', '=', 'Staff RnD')->first();
        for ($i = 1; $i <= 6; $i++) {
            $this->createUser($staffRnD->id, $listId, $randomRate, 'staff_rnd' . $i);
        }

        $staffHRD = DB::table('jabatans')
            ->where('name', '=', 'Staff HRD')->first();
        for ($i = 1; $i <= 6; $i++) {
            $this->createUser($staffHRD->id, $listId, $randomRate, 'staff_hrd' . $i);
        }

        $staffTrading = DB::table('jabatans')
            ->where('name', '=', 'Staff Trading')->first();
        for ($i = 1; $i <= 6; $i++) {
            $this->createUser($staffTrading->id, $listId, $randomRate, 'staff_trading' . $i);
        }
        $staffPRD = DB::table('jabatans')
            ->where('name', '=', 'Staff PRD')->first();
        for ($i = 1; $i <= 6; $i++) {
            $this->createUser($staffPRD->id, $listId, $randomRate, 'staff_prd' . $i);
        }

        $anggotaMagang = DB::table('jabatans')->where('name', '=', 'Anggota Magang')->first();
        for ($i = 1; $i <= 50; $i++) {
            $this->createUser($anggotaMagang->id, $listId, $randomRate, 'anggota' . $i);
        }
    }
}
