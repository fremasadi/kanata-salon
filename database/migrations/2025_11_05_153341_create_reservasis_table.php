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
        Schema::create('reservasis', function (Blueprint $table) {
            $table->id();
            $table->string('name_pelanggan');
            $table->json('layanan_id'); // bisa berisi banyak layanan
            $table->date('tanggal');
            $table->time('jam');
            $table->enum('jenis', ['Online', 'Walk-in'])->default('Walk-in');
            $table->enum('status', ['Menunggu', 'Dikonfirmasi', 'Berjalan', 'Selesai', 'Batal'])->default('Menunggu');
            $table->enum('status_pembayaran', ['DP', 'Lunas'])
                ->default('DP')
                ->after('status');
            $table->decimal('jumlah_pembayaran', 12, 2)
                ->default(0)
                ->after('status_pembayaran');
            $table->decimal('total_harga', 12, 2)->default(0);
            $table->foreignId('pegawai_pj_id')->constrained('pegawais')->onDelete('cascade');
            $table->json('pegawai_helper_id')->nullable(); // bisa kosong
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservasis');
    }
};
