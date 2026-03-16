<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Reservasi;
use App\Models\Pegawai;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    // Konstanta DP
    const DP_AMOUNT = 50000;
    const MIN_TOTAL_FOR_DP = 50000;
    
    public function index()
    {
        $cartItems = Cart::get();
        
        if (count($cartItems) == 0) {
            return redirect()->route('landing')->with('error', 'Keranjang Anda kosong');
        }
        
        $total = Cart::total();
        $totalDuration = Cart::totalDuration();

        // Tentukan apakah DP tersedia
        $dpAvailable = $total > self::MIN_TOTAL_FOR_DP;
        $dpAmount = self::DP_AMOUNT;

        // Cek apakah ada layanan Kelompok (butuh helper)
        $hasKelompok = collect($cartItems)->contains(
            fn($item) => strtolower($item['kategori'] ?? '') === 'kelompok'
        );

        return view('front.checkout.index', compact('cartItems', 'total', 'totalDuration', 'dpAvailable', 'dpAmount', 'hasKelompok'));
    }
    
    public function store(Request $request)
    {
        $cartItems = Cart::get();
        
        if (empty($cartItems) || count($cartItems) == 0) {
            return redirect()->route('landing')->with('error', 'Keranjang Anda kosong');
        }
        
        $total = Cart::total();
        $dpAvailable = $total > self::MIN_TOTAL_FOR_DP;
        
        // Validasi custom untuk jenis pembayaran
        $rules = [
            'tanggal'        => 'required|date|after_or_equal:today',
            'jam'            => 'required',
            'catatan'        => 'nullable|string|max:500',
        ];

        // Jika DP tersedia, jenis_pembayaran wajib dipilih
        if ($dpAvailable) {
            $rules['jenis_pembayaran'] = 'required|in:DP,Lunas';
        }

        $request->validate($rules, [
            'tanggal.required'        => 'Tanggal reservasi wajib diisi',
            'tanggal.after_or_equal'  => 'Tanggal reservasi minimal hari ini',
            'jam.required'            => 'Jam reservasi wajib diisi',
            'jenis_pembayaran.required' => 'Pilih jenis pembayaran',
        ]);
        
        // Jika total <= 50rb, paksa Lunas
        $jenisPembayaran = 'Lunas';
        if ($dpAvailable && $request->jenis_pembayaran == 'DP') {
            $jenisPembayaran = 'DP';
        }
        
        // ✅ Ambil ID layanan dengan quantity (duplikasi sesuai quantity)
        $layananIds = [];
        foreach ($cartItems as $item) {
            // Tambahkan ID sebanyak quantity
            for ($i = 0; $i < $item['quantity']; $i++) {
                $layananIds[] = $item['id'];
            }
        }
        
        // Hitung jumlah pembayaran
        $jumlahPembayaran = $jenisPembayaran == 'DP' ? self::DP_AMOUNT : $total;
        
        try {
            DB::beginTransaction();
            
            // Buat reservasi
            $reservasi = Reservasi::create([
                'name_pelanggan' => Auth::user()->name,
                'layanan_id' => json_encode($layananIds),
                'tanggal' => $request->tanggal,
                'jam' => $request->jam,
                'jenis' => 'Online',
                'status' => 'Menunggu',
                'status_pembayaran' => $jenisPembayaran,
                'jumlah_pembayaran' => $jumlahPembayaran,
                'total_harga' => $total,
                'catatan' => $request->catatan,
            ]);
            
            Cart::clear();
            
            DB::commit();
            
            return redirect()->route('payment.create', $reservasi->id)
                ->with('success', 'Reservasi berhasil dibuat. Silakan lanjutkan pembayaran.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating reservation: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * AJAX: Kembalikan slot waktu yang tersedia berdasarkan tanggal dan isi cart.
     * Request: { tanggal: 'Y-m-d' }
     * Response JSON: { slots, by_slot, total_durasi }
     */
    public function availableSlots(Request $request)
    {
        $request->validate(['tanggal' => 'required|date']);

        $cartItems = Cart::get();
        if (empty($cartItems)) {
            return response()->json(['slots' => [], 'by_slot' => [], 'total_durasi' => 0]);
        }

        // Kumpulkan semua layanan_id dari cart (dengan duplikat sesuai quantity)
        $layananIds = [];
        foreach ($cartItems as $item) {
            for ($i = 0; $i < $item['quantity']; $i++) {
                $layananIds[] = $item['id'];
            }
        }

        $result = (new AvailabilityService())->getAvailableSlots($request->tanggal, $layananIds);

        return response()->json($result);
    }
}