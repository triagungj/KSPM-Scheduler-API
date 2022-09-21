<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'schedule_candidate_id'
    ];

    protected $casts = [
        'id' => 'string'
    ];

    public function scheduleCandidate()
    {
        return $this->hasOne(ScheduleCandidate::class, 'schedule_request_id', 'id');
    }
}
