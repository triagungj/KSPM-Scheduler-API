<?php

namespace Database\Seeders;

use App\Models\JabatanCategory;
use App\Models\Partisipant;
use App\Models\Petugas;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'username' => 'triagungj',
            'password' => Hash::make('123456'),
            'is_petugas' => true,
        ]);

        Petugas::create([
            'username' => 'triagungj',
            'name' => 'Tri Agung J',
            'phone_number' => '6282327495261',
            'is_superuser' => true,
        ]);

        User::create([
            'username' => 'validator',
            'password' => Hash::make('123456'),
            'is_petugas' => true,
        ]);

        Petugas::create([
            'username' => 'validator',
            'name' => 'User Validator',
            'phone_number' => '6282327495261',
            'is_superuser' => false,
        ]);

        User::create([
            'username' => 'faradhika',
            'password' => Hash::make('123456'),
            'is_petugas' => false,
        ]);

        Partisipant::create([
            'username' => 'faradhika',
            'name' => 'Faradhika M. D.',
            'member_id' => 'P1234215124213',
            'phone_number' => '6282327495261',
            'jabatan_id' => 1
        ]);
    }
}
