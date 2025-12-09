<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Komisi extends Model
{
    use HasFactory;

        protected $primaryKey = 'komisi_id';

        protected $fillable = [
            'reservasi_id',
            'pegawai_id',
            'persentase',
            'jumlah',
            'peran',
        ];

        /**
         * Relasi ke Reservasi
         */
        public function reservasi()
        {
            return $this->belongsTo(Reservasi::class, 'reservasi_id');
        }

        /**
         * Relasi ke Pegawai
         */
        public function pegawai()
        {
            return $this->belongsTo(Pegawai::class, 'pegawai_id');
        }

        /**
         * Hitung jumlah komisi berdasarkan persentase dan total reservasi
         */
        public function hitungJumlah(float $totalReservasi): float
        {
            return $this->persentase
                ? round(($this->persentase / 100) * $totalReservasi, 2)
                : $this->jumlah;
        }
}
