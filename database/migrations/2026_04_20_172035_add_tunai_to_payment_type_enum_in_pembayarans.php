<?php

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
        DB::statement("ALTER TABLE pembayarans MODIFY COLUMN payment_type ENUM('credit_card','bank_transfer','echannel','gopay','qris','shopeepay','tunai','other') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pembayarans MODIFY COLUMN payment_type ENUM('credit_card','bank_transfer','echannel','gopay','qris','shopeepay','other') NULL");
    }
};
