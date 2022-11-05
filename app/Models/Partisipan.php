<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partisipan extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'username',
        'jabatan_id',
        'name',
        'member_id',
        'phone_number',
        'avatar_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }

    public function jabatans()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id', 'id');
    }
    protected $casts = [
        'id' => 'string'
    ];

    public function scheduleRequest()
    {
        return $this->belongsTo(ScheduleRequest::class, 'id', 'partisipan_id');
    }
}
