<?php

namespace Database\Seeders;

use App\Models\Petugas;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PetugasSeeder extends Seeder
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
    }
}
