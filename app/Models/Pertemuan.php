<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pertemuan extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name'
    ];
    protected $casts = [
        'id' => 'string'
    ];

    public function sesi()
    {
        return $this->belongsToMany(Sesi::class);
    }
}
