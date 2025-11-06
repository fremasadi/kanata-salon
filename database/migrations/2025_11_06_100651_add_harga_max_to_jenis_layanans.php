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
            $table->decimal('harga_max', 15, 2)->nullable()->after('harga');
                $table->string('jenis')->nullable()->after('harga_max'); // Bisa diisi Styling, Coloring, Treatment, dll.

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
