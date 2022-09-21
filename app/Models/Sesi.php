<?php

namespace App\Models;

use App\Models\Enum\DayEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sesi extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'pertemuan_id',
        'hari',
        'waktu',
    ];

    protected $casts = [
        'hari' => DayEnum::class,
    ];

    public function pertemuan()
    {
        $this->belongsTo(Pertemuan::class, 'pertemuan_id', 'id');
    }

    public function next()
    {
        return $this::where('id', '>', $this->id)->orderBy('id', 'asc')->first();
    }
    public  function previous()
    {
        return $this::where('id', '<', $this->id)->orderBy('id', 'desc')->first();
    }
}
