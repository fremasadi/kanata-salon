<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservasi_id',
        'order_id',
        'transaction_id',
        'gross_amount',
        'payment_type',
        'bank',
        'va_number',
        'transaction_status',
        'fraud_status',
        'transaction_time',
        'settlement_time',
        'payment_url',
        'midtrans_response',
        'notes',
    ];

    protected $casts = [
        'midtrans_response' => 'array',
        'transaction_time' => 'datetime',
        'settlement_time' => 'datetime',
    ];

    // Relationships
    public function reservasi()
    {
        return $this->belongsTo(Reservasi::class);
    }

    // Helper Methods
    public function isPending()
    {
        return $this->transaction_status === 'pending';
    }

    public function isSuccess()
    {
        return in_array($this->transaction_status, ['settlement', 'capture']);
    }

    public function isFailed()
    {
        return in_array($this->transaction_status, ['deny', 'expire', 'cancel']);
    }

    public function getStatusLabel()
    {
        $labels = [
            'pending' => 'Menunggu Pembayaran',
            'settlement' => 'Pembayaran Berhasil',
            'capture' => 'Pembayaran Berhasil',
            'deny' => 'Pembayaran Ditolak',
            'expire' => 'Pembayaran Kadaluarsa',
            'cancel' => 'Pembayaran Dibatalkan',
        ];

        return $labels[$this->transaction_status] ?? 'Status Tidak Diketahui';
    }

    public function getStatusBadgeClass()
    {
        $classes = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'settlement' => 'bg-green-100 text-green-800',
            'capture' => 'bg-green-100 text-green-800',
            'deny' => 'bg-red-100 text-red-800',
            'expire' => 'bg-gray-100 text-gray-800',
            'cancel' => 'bg-red-100 text-red-800',
        ];

        return $classes[$this->transaction_status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getPaymentMethodLabel()
    {
        $methods = [
            'credit_card' => 'Kartu Kredit',
            'bank_transfer' => 'Transfer Bank',
            'echannel' => 'Mandiri Bill',
            'bca_va' => 'BCA Virtual Account',
            'bni_va' => 'BNI Virtual Account',
            'bri_va' => 'BRI Virtual Account',
            'permata_va' => 'Permata Virtual Account',
            'gopay' => 'GoPay',
            'shopeepay' => 'ShopeePay',
            'qris' => 'QRIS',
        ];

        return $methods[$this->payment_type] ?? ($this->payment_type ?? 'Belum Dipilih');
    }
}