<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use App\Models\JabatanCategory;
use App\Models\Partisipant;
use App\Models\Petugas;
use App\Models\ScheduleRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
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
            'is_superuser' => true,
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
            'is_superuser' => false,
        ]);

        $userUuid = Str::uuid();
        User::create([
            'id' => Str::uuid(),
            'username' => 'faradhika',
            'password' => Hash::make('12345678'),
            'is_petugas' => false,
        ]);

        $jabatan = DB::table('jabatans')
            ->inRandomOrder()
            ->first();
        Partisipant::create([
            'id' => $userUuid,
            'username' => 'faradhika',
            'name' => 'Faradhika M. D.',
            'member_id' => 'P1234215124213',
            'phone_number' => '6282327495261',
            'jabatan_id' => $jabatan->id,
        ]);

        ScheduleRequest::create([
            'id' => Str::uuid(),
            'partisipant_id' => $userUuid,
        ]);
    }
}
