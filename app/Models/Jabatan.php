<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'jabatan_category_id',
    ];

    public function jabatan_categories()
    {
        $this->belongsTo(JabatanCategory::class, 'id', 'jabatan_category_id');
    }
}
