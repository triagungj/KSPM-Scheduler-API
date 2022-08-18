<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partisipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'username',
        'jabatan_id',
        'name',
        'member_id',
        'phone_number',
    ];

    public function user()
    {
        $this->belongsTo(User::class, 'username', 'username');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id', 'id');
    }
}
