<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'shift_id',
        'layanan_id',
        'kontak',
    ];

    protected $casts = [
        'layanan_id' => 'array',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke Shift
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    // Relasi ke JenisLayanan (mengakses data dari JSON array)
    public function jenisLayanans()
    {
        return JenisLayanan::whereIn('id', $this->layanan_id ?? []);
    }

    // Helper method untuk mendapatkan array ID layanan
    public function getLayananIdsAttribute()
    {
        return $this->layanan_id ?? [];
    }

    
}