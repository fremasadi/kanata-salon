<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Reservasi;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $midtrans;

    public function __construct(MidtransService $midtrans)
    {
        $this->midtrans = $midtrans;
    }

    /**
     * Create Payment
     */
    /**
 * Create Payment (Redirect dari Checkout)
 */
public function create($reservasiId)
{
    $reservasi = Reservasi::with('pegawaiPJ')->findOrFail($reservasiId);
    
    // Check if already has pending payment
    $existingPayment = Pembayaran::where('reservasi_id', $reservasi->id)
        ->whereIn('transaction_status', ['pending', 'settlement', 'capture'])
        ->first();
        
    if ($existingPayment) {
        return redirect()->route('payment.show', $existingPayment->id);
    }

    // Generate Order ID
    $orderId = 'ORDER-' . $reservasi->id . '-' . time();

    // Customer Details
    $customerDetails = [
        'first_name' => $reservasi->name_pelanggan,
        'email' => auth()->user()->email ?? 'noreply@kanatasalon.com',
        'phone' => auth()->user()->phone ?? '08123456789',
    ];

    // Item Details - DECODE JSON layanan_id dan hitung dengan quantity
    $layananIds = is_string($reservasi->layanan_id) 
        ? json_decode($reservasi->layanan_id, true) 
        : $reservasi->layanan_id;
    
    // Hitung berapa kali muncul setiap layanan (untuk quantity)
    $layananCount = array_count_values($layananIds);
    
    // Get unique layanan
    $uniqueLayananIds = array_unique($layananIds);
    $layananItems = \App\Models\JenisLayanan::whereIn('id', $uniqueLayananIds)->get();
    
    if ($layananItems->isEmpty()) {
        return back()->with('error', 'Layanan tidak ditemukan');
    }
    
    $itemDetails = [];
    foreach ($layananItems as $layanan) {
        $quantity = $layananCount[$layanan->id] ?? 1; // Get quantity dari cart
        
        $itemDetails[] = [
            'id' => $layanan->id,
            'price' => (int) $layanan->harga,
            'quantity' => $quantity, // ✅ Gunakan quantity yang benar
            'name' => $layanan->name,
        ];
    }

    // Create transaction
    $result = $this->midtrans->createTransaction(
        $orderId,
        (int) $reservasi->jumlah_pembayaran,
        $customerDetails,
        $itemDetails
    );

    if (!$result['success']) {
        return back()->with('error', 'Gagal membuat pembayaran: ' . $result['message']);
    }

    // Save to database
    $pembayaran = Pembayaran::create([
        'reservasi_id' => $reservasi->id,
        'order_id' => $orderId,
        'gross_amount' => $reservasi->jumlah_pembayaran,
        'transaction_status' => 'pending',
        'payment_url' => $result['payment_url'],
        'midtrans_response' => json_encode($result),
    ]);

    return redirect()->route('payment.show', $pembayaran->id);
}
    /**
     * Show Payment Page
     */
    public function show($id)
    {
        $pembayaran = Pembayaran::with('reservasi')->findOrFail($id);
        
        // Get latest status from Midtrans
        $statusResult = $this->midtrans->getTransactionStatus($pembayaran->order_id);
        
        if ($statusResult['success']) {
            $this->updatePaymentStatus($pembayaran, $statusResult['data']);
        }

        return view('payment.show', compact('pembayaran'));
    }

    /**
     * Payment Callback from Midtrans
     */
    public function callback(Request $request)
    {
        $serverKey = config('midtrans.server_key');
        $hashed = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        
        if ($hashed !== $request->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $pembayaran = Pembayaran::where('order_id', $request->order_id)->first();
        
        if (!$pembayaran) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $this->updatePaymentStatus($pembayaran, $request->all());

        return response()->json(['message' => 'Callback processed']);
    }

    /**
     * Payment Finish (User redirected here after payment)
     */
    public function finish(Request $request)
    {
        $orderId = $request->order_id;
        $pembayaran = Pembayaran::where('order_id', $orderId)->first();

        if (!$pembayaran) {
            return redirect()->route('landing')->with('error', 'Pembayaran tidak ditemukan');
        }

        // Get latest status
        $statusResult = $this->midtrans->getTransactionStatus($orderId);
        
        if ($statusResult['success']) {
            $this->updatePaymentStatus($pembayaran, $statusResult['data']);
        }

        return redirect()->route('payment.show', $pembayaran->id);
    }

    /**
     * Update Payment Status
     */
    private function updatePaymentStatus($pembayaran, $data)
    {
        $transactionStatus = $data->transaction_status ?? $data['transaction_status'] ?? 'pending';
        $fraudStatus = $data->fraud_status ?? $data['fraud_status'] ?? null;
        $paymentType = $data->payment_type ?? $data['payment_type'] ?? null;

        $updateData = [
            'transaction_id' => $data->transaction_id ?? $data['transaction_id'] ?? null,
            'transaction_status' => $transactionStatus,
            'fraud_status' => $fraudStatus,
            'payment_type' => $paymentType,
            'midtrans_response' => is_array($data) ? $data : json_decode(json_encode($data), true),
        ];

        // Bank info for VA
        if (isset($data->va_numbers) || isset($data['va_numbers'])) {
            $vaNumbers = $data->va_numbers ?? $data['va_numbers'];
            if (!empty($vaNumbers)) {
                $vaNumber = is_array($vaNumbers) ? $vaNumbers[0] : $vaNumbers[0];
                $updateData['bank'] = $vaNumber->bank ?? $vaNumber['bank'] ?? null;
                $updateData['va_number'] = $vaNumber->va_number ?? $vaNumber['va_number'] ?? null;
            }
        }

        // Transaction time
        if (isset($data->transaction_time) || isset($data['transaction_time'])) {
            $updateData['transaction_time'] = $data->transaction_time ?? $data['transaction_time'];
        }

        // Settlement time
        if ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
            $updateData['settlement_time'] = now();
            
            // Update reservasi status pembayaran
            $pembayaran->reservasi->update([
                'status_pembayaran' => 'DP',
                'jumlah_pembayaran' => $pembayaran->gross_amount,
            ]);
        }

        $pembayaran->update($updateData);
    }

    /**
 * Check Payment Status
 */
public function checkStatus($id)
{
    try {
        $pembayaran = Pembayaran::with('reservasi')->findOrFail($id);
        
        // Get latest status from Midtrans
        $statusResult = $this->midtrans->getTransactionStatus($pembayaran->order_id);
        
        \Log::info('Check Payment Status', [
            'order_id' => $pembayaran->order_id,
            'current_status' => $pembayaran->transaction_status,
            'midtrans_result' => $statusResult
        ]);
        
        if ($statusResult['success']) {
            $this->updatePaymentStatus($pembayaran, $statusResult['data']);
            
            // Refresh data setelah update
            $pembayaran->refresh();
            
            return response()->json([
                'success' => true,
                'status' => $pembayaran->transaction_status,
                'message' => $pembayaran->getStatusLabel(),
                'is_success' => $pembayaran->isSuccess(),
                'debug' => [
                    'order_id' => $pembayaran->order_id,
                    'transaction_status' => $pembayaran->transaction_status,
                    'midtrans_response' => $statusResult
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengecek status pembayaran: ' . ($statusResult['message'] ?? 'Unknown error'),
        ], 500);
        
    } catch (\Exception $e) {
        \Log::error('Error checking payment status: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
        ], 500);
    }
}
}