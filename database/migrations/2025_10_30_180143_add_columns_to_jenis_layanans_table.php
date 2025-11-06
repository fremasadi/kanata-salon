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
        Schema::table('jenis_layanans', function (Blueprint $table) {
            $table->decimal('harga', 10, 2)->nullable()->after('name');
            $table->integer('durasi_menit')->nullable()->after('harga');
            $table->text('deskripsi')->nullable()->after('durasi_menit');
            $table->enum('kategori', ['Tunggal', 'Kelompok'])->default('Tunggal')->after('deskripsi');
            $table->string('image')->nullable()->after('kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jenis_layanans', function (Blueprint $table) {
            //
        });
    }
};
