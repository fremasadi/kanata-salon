<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_pelanggan',
        'layanan_id',
        'tanggal',
        'jam',
        'jenis',
        'status',
        'status_pembayaran',
        'jumlah_pembayaran',
        'total_harga',
        'pegawai_pj_id',
        'pegawai_helper_id',
    ];

    protected $casts = [
        'layanan_id' => 'array',
        'pegawai_helper_id' => 'array',
        'tanggal' => 'date',
        'total_harga' => 'decimal:2',
        'jumlah_pembayaran' => 'decimal:2',
    ];

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class);
    }


    // Relasi ke pegawai penanggung jawab
    public function pegawaiPJ()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_pj_id');
    }

    // Relasi ke pegawai helper (JSON)
    public function pegawaiHelpers()
    {
        return Pegawai::whereIn('id', $this->pegawai_helper_id ?? [])->get();
    }

    // Ambil data layanan dari JSON
    public function layananList()
{
    $ids = $this->layanan_id;

    // Pastikan hasilnya array (handle jika masih JSON string)
    if (is_string($ids)) {
        $ids = json_decode($ids, true);
    }

    if (!is_array($ids)) {
        $ids = [];
    }

    return JenisLayanan::whereIn('id', $ids)->get();
}


    // Getter format harga
    public function getTotalHargaFormattedAttribute()
    {
        return number_format($this->total_harga, 0, ',', '.');
    }

    public function getJumlahPembayaranFormattedAttribute()
    {
        return number_format($this->jumlah_pembayaran, 0, ',', '.');
    }
    // Di Model Reservasi
public function getPegawaiHelperIdAttribute($value)
{
    if (is_null($value)) {
        return [];
    }
    
    $decoded = json_decode($value, true);
    
    // Jika masih string (double encoded), decode lagi
    if (is_string($decoded)) {
        $decoded = json_decode($decoded, true);
    }
    
    return is_array($decoded) ? $decoded : [];
}

public function getLayananIdAttribute($value)
{
    if (is_null($value)) {
        return [];
    }
    
    $decoded = json_decode($value, true);
    
    // Jika masih string (double encoded), decode lagi
    if (is_string($decoded)) {
        $decoded = json_decode($decoded, true);
    }
    
    return is_array($decoded) ? $decoded : [];
}
}
