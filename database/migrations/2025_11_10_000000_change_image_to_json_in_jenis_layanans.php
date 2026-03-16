<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Konversi data lama (string path) menjadi JSON array
        DB::table('jenis_layanans')
            ->whereNotNull('image')
            ->get()
            ->each(function ($row) {
                $val = trim($row->image);
                // Jika belum berupa JSON array, bungkus dalam array
                if (!str_starts_with($val, '[')) {
                    DB::table('jenis_layanans')
                        ->where('id', $row->id)
                        ->update(['image' => json_encode([$val])]);
                }
            });

        // Ubah tipe kolom menjadi JSON
        DB::statement('ALTER TABLE jenis_layanans MODIFY image JSON NULL');
    }

    public function down(): void
    {
        // Kembalikan ke VARCHAR dengan mengambil elemen pertama
        DB::statement('ALTER TABLE jenis_layanans MODIFY image VARCHAR(255) NULL');

        DB::table('jenis_layanans')
            ->whereNotNull('image')
            ->get()
            ->each(function ($row) {
                $decoded = json_decode($row->image, true);
                if (is_array($decoded)) {
                    DB::table('jenis_layanans')
                        ->where('id', $row->id)
                        ->update(['image' => $decoded[0] ?? null]);
                }
            });
    }
};
