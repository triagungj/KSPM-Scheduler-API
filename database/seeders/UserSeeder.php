<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use App\Models\Partisipant;
use App\Models\Petugas;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $ketuaUmum = DB::table(
            'jabatans'
        )->where('name', '=', 'Ketua Umum')->first();

        User::create([
            'id' => Str::uuid()->toString(),
            'username' => 'triagungj',
            'password' => Hash::make('12345678'),
            'is_petugas' => true,
        ]);

        Petugas::create([
            'id' => Str::uuid()->toString(),
            'username' => 'triagungj',
            'name' => 'Tri Agung J',
            'phone_number' => '6282327495261',
            'is_superuser' => true,
        ]);

        User::create([
            'id' => Str::uuid()->toString(),
            'username' => 'validator',
            'password' => Hash::make('12345678'),
            'is_petugas' => true,
        ]);

        Petugas::create([
            'id' => Str::uuid()->toString(),
            'username' => 'validator',
            'name' => 'User Validator',
            'phone_number' => '6282327495261',
            'is_superuser' => false,
        ]);

        User::create([
            'id' => Str::uuid()->toString(),
            'username' => 'faradhika',
            'password' => Hash::make('12345678'),
            'is_petugas' => false,
        ]);

        Partisipant::create([
            'id' => Str::uuid()->toString(),
            'username' => 'faradhika',
            'name' => 'Faradhika M. D.',
            'member_id' => 'P1234215124213',
            'phone_number' => '6282327495261',
            'jabatan_id' => $ketuaUmum->id
        ]);
    }
}
