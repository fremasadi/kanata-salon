<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftHistory extends Model
{
    protected $table = 'shift_histories';

    protected $fillable = [
        'pegawai_id',
        'shift_id',
        'tanggal',
        'hari',
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
