<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Reservasi;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $cartItems = Cart::get();
        
        if (count($cartItems) == 0) {
            return redirect()->route('landing')->with('error', 'Keranjang Anda kosong');
        }
        
        $total = Cart::total();
        $totalDuration = Cart::totalDuration();
        
        return view('front.checkout.index', compact('cartItems', 'total', 'totalDuration'));
    }
    
    public function store(Request $request)
{
    $request->validate([
        'tanggal' => 'required|date|after_or_equal:today',
        'jam' => 'required',
        'jenis_pembayaran' => 'required|in:DP,Lunas',
        'catatan' => 'nullable|string|max:500',
    ], [
        'tanggal.required' => 'Tanggal reservasi wajib diisi',
        'tanggal.after_or_equal' => 'Tanggal reservasi minimal hari ini',
        'jam.required' => 'Jam reservasi wajib diisi',
        'jenis_pembayaran.required' => 'Pilih jenis pembayaran',
    ]);
    
    $cartItems = Cart::get();
    
    if (empty($cartItems) || count($cartItems) == 0) {
        return redirect()->route('landing')->with('error', 'Keranjang Anda kosong');
    }
    
    $total = Cart::total();
    
    // ✅ Ambil ID layanan dengan quantity (duplikasi sesuai quantity)
    $layananIds = [];
    foreach ($cartItems as $item) {
        // Tambahkan ID sebanyak quantity
        for ($i = 0; $i < $item['quantity']; $i++) {
            $layananIds[] = $item['id'];
        }
    }
    
    // Hitung jumlah pembayaran
    $jumlahPembayaran = $request->jenis_pembayaran == 'DP' ? $total * 0.5 : $total;
    
    try {
        DB::beginTransaction();
        
        // Buat reservasi
        $reservasi = Reservasi::create([
            'name_pelanggan' => Auth::user()->name,
            'layanan_id' => json_encode($layananIds), // ✅ Sekarang berisi [2,2,2,2,2]
            'tanggal' => $request->tanggal,
            'jam' => $request->jam,
            'jenis' => 'Online',
            'status' => 'Menunggu',
            'status_pembayaran' => 'DP',
            'jumlah_pembayaran' => $jumlahPembayaran,
            'total_harga' => $total,
            'pegawai_pj_id' => null,
            'pegawai_helper_id' => null,
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
}