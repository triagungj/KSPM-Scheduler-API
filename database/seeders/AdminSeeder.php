<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{

    public function run()
    {
        Admin::create([
            'id' => Str::uuid(),
            'username' => 'triagungjr',
            'password' => Hash::make('12345678'),
            'phone_number' => '6282327495261',
        ]);
    }
}
