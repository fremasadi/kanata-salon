<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservasi;
use App\Models\Pegawai;
use App\Models\JenisLayanan;
use App\Models\Shift;
use App\Services\AvailabilityService;
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
        'pegawai_pj_id' => 'nullable|exists:pegawais,id',
        'pegawai_helper_id' => 'nullable|array',
        'status_pembayaran' => 'required|in:DP,Lunas',
        'jumlah_pembayaran' => 'nullable|numeric|min:0',
    ]);


    $data['pegawai_helper_id'] = $data['pegawai_helper_id'] ?? [];
    $data['pegawai_pj_id'] = $data['pegawai_pj_id'] ?? null;
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

    public function show(Reservasi $reservasi)
    {
        $layananList = $reservasi->layananList();
        $helperList  = $reservasi->pegawaiHelpers();
        return view('admin.reservasi.show', compact('reservasi', 'layananList', 'helperList'));
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

    public function updateStatus(Request $request, Reservasi $reservasi)
    {
        $request->validate([
            'status' => 'required|in:Dikonfirmasi,Batal',
        ]);

        $newStatus = $request->status;
        // No guard needed — Dikonfirmasi and Batal are always allowed

        $reservasi->update(['status' => $newStatus]);

        return back()->with('success', 'Status reservasi berhasil diubah menjadi "' . $newStatus . '".');
    }

    public function showPelunasan(Reservasi $reservasi)
    {
        abort_if(
            $reservasi->jenis !== 'Online' || $reservasi->status_pembayaran === 'Lunas' || $reservasi->status !== 'Berjalan',
            403,
            'Pelunasan hanya tersedia untuk reservasi Online yang sedang Berjalan dan belum Lunas.'
        );

        $layananList = $reservasi->layananList();
        return view('admin.reservasi.pelunasan', compact('reservasi', 'layananList'));
    }

    public function prosesPelunasan(Request $request, Reservasi $reservasi)
    {
        abort_if(
            $reservasi->jenis !== 'Online' || $reservasi->status_pembayaran === 'Lunas' || $reservasi->status !== 'Berjalan',
            403,
            'Pelunasan hanya tersedia untuk reservasi Online yang sedang Berjalan dan belum Lunas.'
        );

        $request->validate([
            'harga_final'   => 'required|array',
            'harga_final.*' => 'required|numeric|min:0',
        ]);

        $totalFinal = array_sum($request->harga_final);
        $sisaTagihan = $totalFinal - $reservasi->jumlah_pembayaran;

        $reservasi->update([
            'total_harga'        => $totalFinal,
            'jumlah_pembayaran'  => $totalFinal,
            'status_pembayaran'  => 'Lunas',
            'status'             => 'Selesai',
        ]);

        return redirect()
            ->route('admin.reservasi.show', $reservasi->id)
            ->with('success', 'Pelunasan berhasil! Sisa tagihan Rp ' . number_format($sisaTagihan, 0, ',', '.') . ' telah diterima. Reservasi ditandai Selesai.');
    }

    public function showMulai(Reservasi $reservasi)
    {
        abort_if(
            in_array($reservasi->status, ['Berjalan', 'Selesai', 'Batal']),
            403,
            'Layanan sudah dimulai atau selesai.'
        );
        $layananList = $reservasi->layananList();
        return view('admin.reservasi.mulai', compact('reservasi', 'layananList'));
    }

    public function prosesMulai(Request $request, Reservasi $reservasi)
    {
        abort_if(
            in_array($reservasi->status, ['Berjalan', 'Selesai', 'Batal']),
            403
        );

        $request->validate([
            'pegawai_pj_id'       => 'required|exists:pegawais,id',
            'pegawai_helper_id'   => 'nullable|array',
            'pegawai_helper_id.*' => 'exists:pegawais,id',
        ]);

        $reservasi->update([
            'pegawai_pj_id'     => $request->pegawai_pj_id,
            'pegawai_helper_id' => $request->pegawai_helper_id ?? [],
            'status'            => 'Berjalan',
        ]);

        return redirect()
            ->route('admin.reservasi.show', $reservasi->id)
            ->with('success', 'Layanan dimulai! PJ dan Helper telah ditetapkan.');
    }

    public function tandaiSelesai(Reservasi $reservasi)
    {
        abort_if(
            $reservasi->status !== 'Berjalan' || $reservasi->status_pembayaran !== 'Lunas',
            403,
            'Hanya reservasi yang sedang Berjalan dan sudah Lunas yang dapat ditandai Selesai.'
        );

        $reservasi->update(['status' => 'Selesai']);

        return redirect()
            ->route('admin.reservasi.show', $reservasi->id)
            ->with('success', 'Reservasi telah diselesaikan.');
    }

    public function destroy(Reservasi $reservasi)
    {
        $reservasi->delete();
        return back()->with('success', 'Reservasi berhasil dihapus!');
    }

    /**
     * AJAX: Kembalikan pegawai yang tersedia untuk tanggal, jam, dan layanan tertentu.
     * Request: { tanggal, jam, layanan_ids[], exclude_id? }
     * Response JSON: [{ id, nama, shift }, ...]
     */
    public function availablePegawai(Request $request)
    {
        $request->validate([
            'tanggal'     => 'required|date',
            'jam'         => 'required|date_format:H:i',
            'layanan_ids' => 'required|array',
        ]);

        $pegawais = (new AvailabilityService())->getAvailablePegawaiForSlot(
            $request->tanggal,
            $request->jam,
            $request->layanan_ids,
            $request->input('exclude_id')
        );

        return response()->json($pegawais);
    }
}
