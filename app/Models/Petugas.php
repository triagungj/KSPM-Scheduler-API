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
        'jabatan_id',
        'name',
        'phone_number',
        'is_superuser'
    ];

    protected $casts = [
        'is_superuser' => 'boolean',
    ];

    public function user()
    {
        $this->belongsTo(User::class, 'username', 'username');
    }
}
