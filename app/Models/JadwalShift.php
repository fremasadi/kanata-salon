<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalShift extends Model
{
    protected $table = 'pegawai_jadwal_shift';

    public $timestamps = false;

    protected $fillable = ['pegawai_id', 'shift_id', 'hari'];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
