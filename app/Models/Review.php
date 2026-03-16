<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservasi_id',
        'jenis_layanan_id',
        'user_id',
        'rating',
        'komentar',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function reservasi()
    {
        return $this->belongsTo(Reservasi::class);
    }

    public function jenisLayanan()
    {
        return $this->belongsTo(JenisLayanan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
