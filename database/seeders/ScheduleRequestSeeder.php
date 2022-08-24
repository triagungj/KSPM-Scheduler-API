<?php

namespace Database\Seeders;

use App\Models\Enum\StatusEnum;
use App\Models\ScheduleRequest;
use Illuminate\Database\Seeder;

class ScheduleRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ScheduleRequest::create([
            'partisipant_id' => 1,
        ]);
    }
}
