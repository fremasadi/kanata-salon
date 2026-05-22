<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PegawaiShiftHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'pegawai_id',
        'shift_id',
        'tanggal',
        'hari',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
