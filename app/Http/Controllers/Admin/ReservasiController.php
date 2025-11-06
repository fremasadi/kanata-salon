<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservasi;
use App\Models\Pegawai;
use App\Models\JenisLayanan;
use App\Models\Shift;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReservasiController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservasi::with(['pegawaiPJ.user']);
        if ($request->filled('jenis')) $query->where('jenis', $request->jenis);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('status_pembayaran')) $query->where('status_pembayaran', $request->status_pembayaran);

        $reservasis = $query->latest()->paginate(10);
        return view('admin.reservasi.index', compact('reservasis'));
    }

    public function create()
    {
        $layanans = JenisLayanan::all();
        $now = Carbon::now();
        $currentTime = $now->format('H:i:s');

        // Deteksi shift aktif
        $shiftAktif = Shift::where(function ($query) use ($currentTime) {
            $query->where(function ($q) use ($currentTime) {
                $q->where('waktu_mulai', '<=', $currentTime)
                  ->where('waktu_selesai', '>=', $currentTime)
                  ->whereRaw('waktu_mulai < waktu_selesai');
            })
            ->orWhere(function ($q) use ($currentTime) {
                $q->where(function ($subQ) use ($currentTime) {
                    $subQ->where('waktu_mulai', '<=', $currentTime)
                         ->whereRaw('waktu_mulai > waktu_selesai');
                })
                ->orWhere(function ($subQ) use ($currentTime) {
                    $subQ->where('waktu_selesai', '>=', $currentTime)
                         ->whereRaw('waktu_mulai > waktu_selesai');
                });
            });
        })->first();

        $pegawais = $shiftAktif
            ? Pegawai::with(['user', 'shift'])->where('shift_id', $shiftAktif->id)->get()
            : collect();

        return view('admin.reservasi.create', compact('layanans', 'pegawais', 'shiftAktif'));
    }

    public function store(Request $request)
{
    $data = $request->validate([
        'name_pelanggan' => 'required|string|max:255',
        'layanan_id' => 'required|array',
        'tanggal' => 'required|date',
        'jam' => 'required',
        'jenis' => 'required|in:Online,Walk-in',
        'total_harga' => 'required|numeric|min:0',
        'pegawai_pj_id' => 'required|exists:pegawais,id',
        'pegawai_helper_id' => 'nullable|array',
        'status_pembayaran' => 'required|in:DP,Lunas',
        'jumlah_pembayaran' => 'nullable|numeric|min:0',
    ]);

   
    $data['pegawai_helper_id'] = $data['pegawai_helper_id'] ?? [];
    $data['status'] = 'Menunggu';

    // Jika status Lunas, jumlah_pembayaran otomatis = total_harga
    if ($data['status_pembayaran'] === 'Lunas') {
        $data['jumlah_pembayaran'] = $data['total_harga'];
    } elseif (empty($data['jumlah_pembayaran'])) {
        // Jika DP tapi kosong, isi default 50% dari total
        $data['jumlah_pembayaran'] = $data['total_harga'] / 2;
    }

    Reservasi::create($data);

    return redirect()->route('admin.reservasi.index')->with('success', 'Reservasi berhasil ditambahkan!');
}

    public function edit(Reservasi $reservasi)
    {
        $layanans = JenisLayanan::all();
        $pegawais = Pegawai::with(['user', 'shift'])->get();
        return view('admin.reservasi.edit', compact('reservasi', 'pegawais', 'layanans'));
    }

    public function update(Request $request, Reservasi $reservasi)
    {
        $data = $request->validate([
            'name_pelanggan' => 'required|string|max:255',
            'layanan_id' => 'required|array',
            'tanggal' => 'required|date',
            'jam' => 'required',
            'jenis' => 'required|in:Online,Walk-in',
            'status' => 'required|in:Menunggu,Dikonfirmasi,Berjalan,Selesai,Batal',
            'status_pembayaran' => 'required|in:DP,Lunas',
            'jumlah_pembayaran' => 'nullable|numeric|min:0',
            'total_harga' => 'required|numeric|min:0',
            'pegawai_pj_id' => 'required|exists:pegawais,id',
            'pegawai_helper_id' => 'nullable|array',
        ]);


        // Update otomatis jumlah pembayaran jika status lunas
        if ($data['status_pembayaran'] === 'Lunas') {
            $data['jumlah_pembayaran'] = $data['total_harga'];
        } elseif (empty($data['jumlah_pembayaran'])) {
            $data['jumlah_pembayaran'] = $reservasi->jumlah_pembayaran ?? ($data['total_harga'] / 2);
        }

        $reservasi->update($data);

        return redirect()->route('admin.reservasi.index')->with('success', 'Reservasi berhasil diperbarui!');
    }

    public function destroy(Reservasi $reservasi)
    {
        $reservasi->delete();
        return back()->with('success', 'Reservasi berhasil dihapus!');
    }
}
