<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Petugas extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'username',
        'name',
        'phone_number',
        'is_superuser'
    ];

    protected $casts = [
        'is_superuser' => 'boolean',
        'id' => 'string'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }

    public function scheduleRequest()
    {
        return $this->hasMany(ScheduleRequest::class);
    }
}
