<?php

use App\Models\Pegawai;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('setting_gajis', function (Blueprint $table) {
            $table->id();
            $table->string('jabatan')->unique();
            $table->string('nama_jabatan');
            $table->decimal('gaji_pokok', 15, 2)->default(0);
            $table->timestamps();
        });

        DB::table('setting_gajis')->insert([
            'jabatan' => Pegawai::JABATAN_PEGAWAI_BIASA,
            'nama_jabatan' => 'Pegawai Biasa',
            'gaji_pokok' => 1200000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting_gajis');
    }
};
