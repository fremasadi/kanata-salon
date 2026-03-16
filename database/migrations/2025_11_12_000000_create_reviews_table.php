<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservasi_id')->constrained('reservasis')->onDelete('cascade');
            $table->foreignId('jenis_layanan_id')->constrained('jenis_layanans')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('rating')->unsigned()->comment('1-5');
            $table->text('komentar')->nullable();
            $table->timestamps();

            // satu layanan hanya bisa direview sekali per reservasi
            $table->unique(['reservasi_id', 'jenis_layanan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
