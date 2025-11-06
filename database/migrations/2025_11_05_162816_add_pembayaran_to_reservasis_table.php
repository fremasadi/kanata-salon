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
        Schema::table('reservasis', function (Blueprint $table) {
            Schema::table('reservasis', function (Blueprint $table) {
            $table->enum('status_pembayaran', ['DP', 'Lunas'])
                ->default('DP')
                ->after('status');
            $table->decimal('jumlah_pembayaran', 12, 2)
                ->default(0)
                ->after('status_pembayaran');
        });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservasis', function (Blueprint $table) {
            //
        });
    }
};
