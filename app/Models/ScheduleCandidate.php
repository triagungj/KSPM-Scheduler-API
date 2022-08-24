<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleCandidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'schedule_request_id',
        'session_id',
    ];

    protected $casts = [
        'status' => StatusEnum::class,
        'id' => 'string'
    ];

    public function scheduleRequest()
    {
        $this->belongsTo(ScheduleRequest::class, 'schedule_request_id', 'id');
    }

    public function session()
    {
        $this->belongsTo(Sesi::class, 'session_id', 'id');
    }
}
