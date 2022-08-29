<?php

namespace App\Models;

use App\Models\Enum\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'partisipant_id',
        'petugas_id',
        'status',
        'catatan_partisipant',
        'catatan_petugas',
        'bukti',
        'tanggal_validasi',
    ];

    protected $casts = [
        'status' => StatusEnum::class,
        'id' => 'string'
    ];

    public function scheduleCandidate()
    {
        return $this->hasMany(ScheduleCandidate::class, 'schedule_request_id', 'id');
    }

    public function petugas()
    {
        return $this->hasOne(Petugas::class, 'id', 'petugas_id');
    }
    public function partisipant()
    {
        return $this->hasOne(Partisipant::class, 'id', 'partisipant_id');
    }
}
