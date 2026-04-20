<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Pegawai extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'layanan_id',
        'kontak',
    ];

    protected $casts = [
        'layanan_id' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jadwalShifts()
    {
        return $this->hasMany(JadwalShift::class);
    }

    /**
     * Ambil shift pegawai pada hari tertentu.
     * $hari: 'senin'|'selasa'|'rabu'|'kamis'|'jumat'|'sabtu'|'minggu'
     */
    public function shiftPadaHari(string $hari): ?Shift
    {
        $jadwal = $this->jadwalShifts->firstWhere('hari', $hari);
        return $jadwal?->shift;
    }

    /**
     * Ambil hari Indonesia dari tanggal (Carbon atau string Y-m-d).
     */
    public static function hariDariTanggal(string $tanggal): string
    {
        $map = [0 => 'minggu', 1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis', 5 => 'jumat', 6 => 'sabtu'];
        return $map[Carbon::parse($tanggal)->dayOfWeek];
    }

    public function jenisLayanans()
    {
        return JenisLayanan::whereIn('id', $this->layanan_id ?? []);
    }

    public function getLayananIdsAttribute()
    {
        return $this->layanan_id ?? [];
    }
}
