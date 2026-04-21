<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlotBlock extends Model
{
    protected $fillable = ['tanggal', 'jam_mulai', 'jam_selesai', 'keterangan'];

    protected $casts = ['tanggal' => 'date'];
}
