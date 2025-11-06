<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gajis', function (Blueprint $table) {
            $table->id('gaji_id'); // Primary key
            $table->foreignId('pegawai_id') // Foreign key ke pegawai
                  ->constrained('pegawais')
                  ->onDelete('cascade');
            $table->date('periode_mulai'); // Periode mulai
            $table->date('periode_selesai'); // Periode selesai
            $table->decimal('gaji_pokok', 15, 2); // Gaji Pokok
            $table->decimal('total_komisi', 15, 2); // Total Komisi
            $table->decimal('total_gaji', 15, 2); // Total Gaji
            $table->enum('status', ['Draft', 'Dibayar', 'Ditunda'])->default('Draft'); // Status gaji
            $table->date('tanggal_dibayar')->nullable(); // Tanggal pembayaran (nullable karena belum dibayar)
            $table->timestamps(); // Timestamps for created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gajis');
    }
};
