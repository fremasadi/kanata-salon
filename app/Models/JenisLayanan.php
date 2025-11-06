<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisLayanan extends Model
{
    use HasFactory;

    protected $table = 'jenis_layanans';

    protected $fillable = [
        'name',
        'harga',
        'harga_max',
            'jenis', // tambahkan ini

        'durasi_menit',
        'deskripsi',
        'kategori',
        'image',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'durasi_menit' => 'integer',
        'harga_max' => 'decimal:2'
    ];
}