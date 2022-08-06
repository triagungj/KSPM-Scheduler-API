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
        'nama',
        'phone_number',
    ];

    public function user(){
        $this->belongsTo(User::class, 'username', 'username');
    }
}
