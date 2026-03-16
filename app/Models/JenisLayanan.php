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
        'harga'        => 'decimal:2',
        'harga_max'    => 'decimal:2',
        'durasi_menit' => 'integer',
        'image'        => 'array',
    ];

    /**
     * Ambil URL gambar pertama, atau null jika tidak ada.
     */
    public function getFirstImageUrlAttribute(): ?string
    {
        $images = $this->image ?? [];
        return !empty($images) ? \Illuminate\Support\Facades\Storage::url($images[0]) : null;
    }
}