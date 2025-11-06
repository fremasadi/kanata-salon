<?php

// app/Models/Gaji.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gaji extends Model
{
    use HasFactory;

    protected $primaryKey = 'gaji_id';

    protected $fillable = [
        'pegawai_id',
        'periode_mulai',
        'periode_selesai',
        'gaji_pokok',
        'total_komisi',
        'total_gaji',
        'status',
        'tanggal_dibayar',
    ];

    // Relasi ke Pegawai
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    // Helper method untuk menghitung total gaji
    public function hitungTotalGaji()
    {
        $this->total_gaji = $this->gaji_pokok + $this->total_komisi;
        $this->save();
    }
}
