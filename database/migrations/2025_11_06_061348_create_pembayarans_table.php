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
        Schema::create('pembayarans', function (Blueprint $table) {
             $table->id();
            $table->foreignId('reservasi_id')->constrained('reservasis')->onDelete('cascade');
            $table->string('order_id')->unique();
            $table->string('transaction_id')->nullable();
            $table->decimal('gross_amount', 12, 2);
            $table->enum('payment_type', ['credit_card', 'bank_transfer', 'echannel', 'gopay', 'qris', 'shopeepay', 'other'])->nullable();
            $table->string('bank')->nullable(); // untuk bank_transfer
            $table->string('va_number')->nullable(); // virtual account number
            $table->enum('transaction_status', ['pending', 'settlement', 'capture', 'deny', 'cancel', 'expire', 'failure'])->default('pending');
            $table->text('fraud_status')->nullable();
            $table->timestamp('transaction_time')->nullable();
            $table->timestamp('settlement_time')->nullable();
            $table->text('payment_url')->nullable(); // untuk redirect payment
            $table->json('midtrans_response')->nullable(); // simpan full response dari midtrans
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('order_id');
            $table->index('transaction_id');
            $table->index('transaction_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
