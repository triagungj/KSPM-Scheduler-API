<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JabatanCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
    ];

    public function jabatans()
    {
        return $this->belongsToMany(Jabatan::class);
    }
}
